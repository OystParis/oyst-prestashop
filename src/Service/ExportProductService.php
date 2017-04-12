<?php

namespace Oyst\Service;

use Oyst;
use Oyst\Api\OystCatalogApi;
use Oyst\Classes\OystCategory;
use Oyst\Classes\OystPrice;
use Oyst\Classes\OystProduct;
use Oyst\Classes\OystSize;
use Oyst\Repository\ProductRepository;
use Product;
use Validate;
use StockAvailable;
use Image;
use Tools;
use Cart;
use Combination;
use Configuration as PSConfiguration;
use Context;
use DateTime;
use Exception;

/**
 * Class Oyst\Service\ExportProductService
 */
class ExportProductService extends AbstractOystService
{
    const EXPORT_ALL_PRODUCT = 0;

    const EXPORT_REGULAR_NUMBER = 512;

    /** @var  ProductRepository */
    protected $productRepository;

    /** @var  string */
    private $weightUnit;

    /** @var  string */
    private $dimensionUnit;

    /** @var  OystCatalogAPI */
    private $oystCatalogAPI;

    /** @var  int */
    private $limitedProduct;

    /**
     * Oyst\Service\ExportProductService constructor.
     * @param Context $context
     * @param Oyst $oyst
     */
    public function __construct(Context $context, Oyst $oyst)
    {
        parent::__construct($context, $oyst);

        $this->setWeightUnit('kg');
        $this->setDimensionUnit('cm');
    }

    /**
     * @param string $weightUnit
     * @return $this
     */
    public function setWeightUnit($weightUnit)
    {
        $this->weightUnit = $weightUnit;

        return $this;
    }

    /**
     * @param string $dimensionUnit
     * @return $this
     */
    public function setDimensionUnit($dimensionUnit)
    {
        $this->dimensionUnit = $dimensionUnit;

        return $this;
    }

    /**
     * @param OystCatalogAPI $oystCatalogAPI
     * @return $this
     */
    public function setCatalogApi(OystCatalogAPI $oystCatalogAPI)
    {
        $this->oystCatalogAPI = $oystCatalogAPI;

        return $this;
    }

    /**
     * @param array $products PrestaShop products
     * @return OystProduct[]
     * @throws Exception
     */
    private function transformProducts($products)
    {
        $product = new Product();

        $this->context->cart = new Cart();
        if (!Validate::isLoadedObject($this->context->currency)) {
            throw new Exception('Bad Currency object, Did you forget to set it ?');
        }

        $oystProducts = [];
        foreach ($products as $productInfo) {

            $oystProduct = new OystProduct();

            $combination = new Combination();
            if ($product->id != $productInfo['id_product']) {
                $product = new Product($productInfo['id_product'], false, $this->context->language->id);
            }

            if ($productInfo['id_product_attribute']) {
                $combination = new Combination($productInfo['id_product_attribute']);
            }

            $oystPrice = new OystPrice($product->getPrice(true, $combination->id), $this->context->currency->iso_code);

            $categories = [];
            // Small cache to use avoid this process with attributes
            if (!isset($categories[$product->id])) {
                $categories = [$product->id => []];
            }

            foreach (Product::getProductCategoriesFull($product->id) as $categoryInfo) {
                $oystCategory = new OystCategory(
                    $categoryInfo['id_category'],
                    $categoryInfo['name'],
                    $categoryInfo['id_category'] == $product->id_category_default
                );
                $categories[$product->id][] = $oystCategory;
            }
            $oystSize = new OystSize(
                $product->height > 0 ? $product->height : 1,
                $product->width > 0 ? $product->width : 1,
                $product->depth > 0 ? $product->depth : 1
            );

            // Combination fields
            $oystProduct->setRef($product->id . '-' . ($combination->id ? $combination->id : 0));
            $oystProduct->setEan(Validate::isLoadedObject($combination) ? $combination->ean13 : $product->ean13);
            $oystProduct->setWeight((Validate::isLoadedObject($combination) ? $combination->weight : $product->weight));

            // Common fields
            $oystProduct->setActive($product->active);
            $oystProduct->setManufacturer($product->manufacturer_name);
            $oystProduct->setSize($oystSize);
            $oystProduct->setCondition(($product->condition == 'used' ? 'reused' : $product->condition));
            $oystProduct->setCategories($categories[$product->id]);
            $oystProduct->setAmountIncludingTax($oystPrice);
            $oystProduct->setAvailableQuantity(StockAvailable::getStockAvailableIdByProductId($product->id, $combination->id));
            $oystProduct->setDescription($product->description);
            $oystProduct->setShortDescription($product->description_short);
            $oystProduct->setUrl($this->context->link->getProductLink($product));

            $images = [];
            foreach (Image::getImages($this->context->language->id, $product->id, $combination->id) as $image) {
                $images[] = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
            }
            if (empty($images)) {
                $images = [Tools::getShopDomain(true) . '/modules/oyst/view/img/no_image.png'];
            }
            $oystProduct->setImages($images);

            $oystProducts[] = clone $oystProduct;
        }

        return $oystProducts;
    }

    /**
     * @param $importId
     * @return bool
     */
    public function export($importId)
    {
        // TODO: Maybe add some log information for this process and store it in a new table ?
        $prestaShopProducts = $this->productRepository->getProductsNotExported($this->limitedProduct);
        $products = $this->transformProducts($prestaShopProducts);

        $this->oystCatalogAPI->postProducts($products);
        $state = false;

        if ($this->oystCatalogAPI->getLastHttpCode() == 200) {
            $state = true;
            $this->productRepository->recordSentProducts($prestaShopProducts, $importId);
            $this->setIsExportCatalogRunning(0 != $this->getTotalProductsRemaining());
        }

        return $state;
    }

    /**
     * @return $this
     */
    public function requestNewExport()
    {
        $this->productRepository->truncateExportTable();
        $this->oystCatalogAPI->notifyImport();

        if ($this->oystCatalogAPI->getLastHttpCode() == 200) {
            PSConfiguration::updateValue('OYST_REQUESTED_CATALOG_DATE', (new DateTime())->format('Y-m-d H:i:s'));
            $this->setExportCatalogState(true);
            $this->oyst->setAdminPanelInformationVisibility(true);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalProductsRemaining()
    {
        $products = $this->productRepository->getProductsNotExported(static::EXPORT_ALL_PRODUCT);

        return count($products);
    }

    /**
     * @return int
     */
    public function getTotalProducts()
    {
        return $this->productRepository->getTotalProducts();
    }

    /**
     * @param $state
     * @return $this
     */
    public function setExportCatalogState($state)
    {
        $state = ((bool) $state) ?
            Configuration::CATALOG_EXPORT_RUNNING :
            Configuration::CATALOG_EXPORT_DONE
        ;
        PSConfiguration::updateValue(Configuration::CATALOG_EXPORT_STATE, $state);

        return $this;
    }

    /**
     * @param int $limitedProduct
     * @return ExportProductService
     */
    public function setLimitedProduct($limitedProduct)
    {
        $this->limitedProduct = $limitedProduct;

        return $this;
    }

    /**
     * @return ProductRepository
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * @param ProductRepository $productRepository
     * @return $this
     */
    public function setProductRepository($productRepository)
    {
        $this->productRepository = $productRepository;

        return $this;
    }
}
