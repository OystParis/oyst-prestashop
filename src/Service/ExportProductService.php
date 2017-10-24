<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

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
 * Class ExportProductService
 */
class ExportProductService extends AbstractOystService
{
    const EXPORT_ALL_PRODUCT = 0;

    const EXPORT_REGULAR_NUMBER = 512;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var string */
    private $weightUnit;

    /** @var string */
    private $dimensionUnit;

    /** @var int */
    private $limitedProduct;

    /** @var ProductTransformer */
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
        // Tricks requirements for PrestaShop
        $this->context->cart = new Cart();
        if (!Validate::isLoadedObject($this->context->currency)) {
            throw new Exception('Bad Currency object, Did you forget to set it ?');
        }

        // Prepare a default cache object (Query is ordered by id ASC :)
        $product = new Product();

        /** @var OystProduct[] $oystProducts */
        $oystProducts = array();
        foreach ($products as $productInfo) {
            // Check if product is already loaded
            if ($product->id != $productInfo['id_product']) {
                $product = new Product($productInfo['id_product'], false, $this->context->language->id);
                if (!Validate::isLoadedObject($product)) {
                    $this->logger->alert(sprintf('Product %d can\'t be found', $productInfo['id_product']));
                    continue;
                }
            }

            // We need the base product first in case it doesn't exist
            if (!isset($oystProducts[$product->id])) {
                if (!($baseOystProduct = $this->productTransformer->transform($product))) {
                    $this->logger->alert(sprintf('Product %d won\'t be exported', $productInfo['id_product']));
                    continue;
                }
                $oystProducts[$product->id] = clone $baseOystProduct;
            }

            // Then we handle combination as variation
            $combination = null;
            if ($productInfo['id_product_attribute']) {
                $combination = new Combination($productInfo['id_product_attribute']);
                if (Validate::isLoadedObject($combination)) {
                    // We still need the original product to get the current price (discount for example)
                    if (($oystProductVariation = $this->productTransformer->transformCombination($product, $combination))) {
                        $oystProducts[$product->id]->addVariation($oystProductVariation);
                    }
                }
            }
        }

        return $oystProducts;
    }

    /**
     * @param int $id_product
     * @param int $id_combination
     * @return OystProduct[]
     * @throws Exception
     */
    public function transformProductLess($id_product, $id_combination)
    {
        // Tricks requirements for PrestaShop
        $this->context->cart = new Cart();
        if (!Validate::isLoadedObject($this->context->currency)) {
            throw new Exception('Bad Currency object, Did you forget to set it ?');
        }

        $product = new Product($id_product, false, $this->context->language->id);

        if (!Validate::isLoadedObject($product)) {
            $this->logger->alert(sprintf('Product %d can\'t be found', $id_product));
            return null;
        }

        if (!($baseOystProduct = $this->productTransformer->transform($product))) {
            $this->logger->alert(sprintf('Product %d won\'t be exported', $id_product));
            return null;
        }
        $oystProduct = clone $baseOystProduct;

        if ($id_combination > 0) {
            $combination = new Combination($id_combination);
            if (Validate::isLoadedObject($combination)) {
                // We still need the original product to get the current price (discount for example)
                if (($oystProductVariation = $this->productTransformer->transformCombination($product, $combination))) {
                    $oystProduct->addVariation($oystProductVariation);
                }
            } else {
                $this->logger->alert(sprintf('Combination %d can\'t be found for produit '.$id_product, $id_combination));
            }
        }

        return $oystProduct;
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

        $json = array(
            'totalCount' => 0,
            'remaining' => 0,
            'state' => false,
        );

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
                $this->logger->warning(sprintf('Product(s) waiting : %s', json_encode($productNotHandled)));
            } else {
                $this->logger->info('Export is over');
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
     * @param bool $state
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
