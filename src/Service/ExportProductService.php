<?php

namespace Oyst\Service;

use Oyst;
use Oyst\Classes\OystProduct;
use Oyst\Transformer\ProductTransformer;
use Oyst\Repository\ProductRepository;
use Product;
use Validate;
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

            if ($oystProduct) {
                $oystProducts[] = clone $oystProduct;
            }
        }

        return $oystProducts;
    }

    /**
     * @param $importId
     * @return array
     * @throws Exception
     */
    public function sendNewProducts($importId)
    {
        if (!$this->productTransformer instanceof ProductTransformer) {
            throw new Exception('Did you forget to set the ProductTransformer ?');
        }

        $json = [
            'totalCount' => 0,
            'remaining' => 0,
            'state' => false,
        ];

        // TODO: Maybe add some log information for this process and store it in a new table ?
        $prestaShopProducts = $this->productRepository->getProductsNotExported($this->limitedProduct);
        $products = $this->transformProducts($prestaShopProducts);

        $this->requester->call('postProducts', array($products));

        $apiClient = $this->requester->getApiClient();
        if ($apiClient->getLastHttpCode() == 200) {
            $json['state'] = true;
            $this->productRepository->recordSentProductsFromOystProductList($this->oyst, $prestaShopProducts, $products, $importId);
            $json['totalCount'] = $this->productRepository->getTotalProducts();
            $productNotHandled = $this->productRepository->getProductsNotExported(static::EXPORT_ALL_PRODUCT);
            $totalProductNotHandled = count($productNotHandled);
            if ($totalProductNotHandled) {
                $this->logger->warning(sprintf('[Export] Product(s) waiting : %s', json_encode($productNotHandled)));
            } else {
                $this->logger->info('[Export] Is over');
                $this->setExportCatalogState(false);
            }
            $json['remaining'] = $totalProductNotHandled;
        } else {
            $json['httpCode'] = $apiClient->getLastHttpCode();
            $json['error'] = $apiClient->getLastError();
        }

        return $json;
    }

    /**
     * @return bool
     */
    public function requestNewExport()
    {
        $this->productRepository->truncateExportTable();

        $this->requester->call('notifyImport');

        $succeed = false;
        if ($this->requester->getApiClient()->getLastHttpCode() == 200) {
            PSConfiguration::updateValue('OYST_REQUESTED_CATALOG_DATE', (new DateTime())->format('Y-m-d H:i:s'));
            $this->setExportCatalogState(true);
            $this->oyst->setAdminPanelInformationVisibility(true);
            $succeed = true;
        }

        return $succeed;
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
     * @param ProductTransformer $productTransformer
     * @return $this
     */
    public function setProductTransformer(ProductTransformer $productTransformer)
    {
        $this->productTransformer = $productTransformer;

        return $this;
    }
}
