<?php

namespace Oyst\Service;

use Oyst;
use Oyst\Api\OystCatalogApi;
use Oyst\Classes\OystProduct;
use Oyst\Transformer\ProductTransformer;
use Oyst\Repository\ProductRepository;
use Product;
use Validate;
use Cart;
use Combination;
use Configuration;
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

    /** @var  ProductTransformer */
    private $productTransformer;

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

            $combination = new Combination();
            if ($product->id != $productInfo['id_product']) {
                $product = new Product($productInfo['id_product'], false, $this->context->language->id);
            }

            if ($productInfo['id_product_attribute']) {
                $combination = new Combination($productInfo['id_product_attribute']);
            }

            $oystProduct = $this->productTransformer->transformWithCombination($product, $combination);

            $oystProducts[] = clone $oystProduct;
        }

        return $oystProducts;
    }

    /**
     * @param $importId
     * @return bool
     * @throws Exception
     */
    public function export($importId)
    {
        if (!$this->productTransformer instanceof ProductTransformer) {
            throw new Exception('Did you forget to set the ProductTransformer ?');
        }

        // TODO: Maybe add some log information for this process and store it in a new table ?
        $prestaShopProducts = $this->productRepository->getProductsNotExported($this->limitedProduct);
        $products = $this->transformProducts($prestaShopProducts);

        $this->requestApi($this->oystCatalogAPI, 'postProducts',
            $products
        );

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

        $this->requestApi($this->oystCatalogAPI, 'notifyImport', null);

        if ($this->oystCatalogAPI->getLastHttpCode() == 200) {
            Configuration::updateValue('OYST_REQUESTED_CATALOG_DATE', (new DateTime())->format('Y-m-d H:i:s'));
            $this->setIsExportCatalogRunning(true);
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
    public function setIsExportCatalogRunning($state)
    {
        $state = ((bool)$state) ? 1 : 0;
        Configuration::updateValue('OYST_IS_EXPORT_STILL_RUNNING', $state);

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
     * @param ProductTransformer $productTransformer
     */
    public function setProductTransformer(ProductTransformer $productTransformer)
    {
        $this->productTransformer = $productTransformer;
    }
}
