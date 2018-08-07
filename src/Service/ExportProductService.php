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
// use Cart;
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
    /** @var ProductRepository */
    protected $productRepository;

    /** @var string */
    private $weightUnit;

    /** @var string */
    private $dimensionUnit;

    /** @var int */
    // private $limitedProduct;

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
    private function transformProducts($products, $quantity = 1)
    {
        // Tricks requirements for PrestaShop
        // $this->context->cart = new Cart();
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
                if (!($baseOystProduct = $this->productTransformer->transform($product, $quantity))) {
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
                    if (($oystProductVariation = $this->productTransformer->transformCombination($product, $combination, $quantity))) {
                        $oystProducts[$product->id]->variation = $oystProductVariation->toArray();
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
    public function transformProductLess($id_product, $id_combination, $quantity, $usetax = true)
    {
        // Tricks requirements for PrestaShop
        // $this->context->cart = new Cart();
        if (!Validate::isLoadedObject($this->context->currency)) {
            throw new Exception('Bad Currency object, Did you forget to set it ?');
        }

        $product = new Product($id_product, false, $this->context->language->id);

        if (!Validate::isLoadedObject($product)) {
            $this->logger->alert(sprintf('Product %d can\'t be found', $id_product));
            return null;
        }

        if (!($oystProduct = $this->productTransformer->transform($product, $quantity, $id_combination, $usetax))) {
            $this->logger->alert(sprintf('Product %d won\'t be exported', $id_product));
            return null;
        }

        if ($id_combination > 0) {
            $combination = new Combination($id_combination);
            if (Validate::isLoadedObject($combination)) {
                // We still need the original product to get the current price (discount for example)
                $oystProductVariation = $this->productTransformer->transformCombination(
                    $product,
                    $combination,
                    $quantity,
                    $usetax
                );
                if ($oystProductVariation) {
                    $variations = array($oystProductVariation);
                    $oystProduct->variations = $variations;
                }
            } else {
                $this->logger->alert(
                    sprintf('Combination %d can\'t be found for produit '.$id_product, $id_combination)
                );
            }
        }

        return $oystProduct;
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
