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

namespace Oyst\Transformer;

use Combination;
use Context;
use Image;
use Oyst\Classes\OystCategory;
use Oyst\Classes\OystPrice;
use Oyst\Classes\OystProduct;
use Oyst\Classes\OystSize;
use Oyst\Repository\ProductRepository;
use Product;
use Psr\Log\AbstractLogger;
use StockAvailable;
use Tools;
use Validate;
use Link;
use Configuration;

/**
 * Class ProductTransformer
 */
class ProductTransformer extends AbstractTransformer
{
    /** @var  AbstractLogger */
    private $logger;

    /** @var  ProductRepository */
    private $productRepository;

    /**
     * ProductTransformer constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param AbstractLogger $logger
     * @return $this
     */
    public function setLogger(AbstractLogger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param Product $product
     *
     * @return OystProduct
     */
    public function transform($product, $quantity = 1, $id_combination = 0)
    {
        $combination = new Combination();

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $protocol_link = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
            $protocol_content = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
            $link = new Link($protocol_link, $protocol_content);
        } else {
            $link = $this->context->link;
        }

        if ($id_combination > 0) {
            $combination = new Combination($id_combination);
            if (!Validate::isLoadedObject($combination)) {
                $this->logger->alert(sprintf('Combination %d can\'t be found for product '.$product->id, $id_combination));
            } else {
                $oystPrice = new OystPrice($product->getPrice(true, $combination->id), $this->context->currency->iso_code);
            }
        } else {
            $oystPrice = new OystPrice($product->getPrice(true), $this->context->currency->iso_code);
        }

        $categories = array();

        foreach (Product::getProductCategoriesFull($product->id) as $categoryInfo) {
            $oystCategory = new OystCategory(
                $categoryInfo['id_category'],
                $categoryInfo['name'],
                $categoryInfo['id_category'] == $product->id_category_default
            );
            $categories[] = $oystCategory;
        }

        if (empty($categories)) {
            if ($this->logger instanceof AbstractLogger) {
                $this->logger->alert(sprintf('No categories for product %d', $product->id));
            }
            return null;
        }

        $oystSize = new OystSize(
            $product->height > 0 ? $product->height : 1,
            $product->width > 0 ? $product->width : 1,
            $product->depth > 0 ? $product->depth : 1
        );

        // Combination fields
        if ($combination && $combination->id) {
            $title = is_array($product->name) ? reset($product->name) : $product->name;

            // Informations
            $attributesInfo = $this->productRepository->getAttributesCombination($combination);
            $informations = array();
            foreach ($attributesInfo as $attributeInfo) {
                $informations[$attributeInfo['name']] = $attributeInfo['value'];
                $title .= ' '.$attributeInfo['value'];
            }

            $reference = (string)$product->id.';'.(string)$combination->id;
            $oystProduct = new OystProduct($reference, $title, $oystPrice, $quantity);
            // $oystProduct->reference = (string)$product->id.';'.(string)$combination->id;
            $oystProduct->__set('ean', $combination->ean13);
            $oystProduct->__set('weight', $combination->weight);
            // Stock
            $stockAvailable = new StockAvailable(StockAvailable::getStockAvailableIdByProductId($product->id, $combination->id));
            $oystProduct->__set('availableQuantity', $stockAvailable->quantity);
            // Images
            $images = array();
            foreach (Image::getImages($this->context->language->id, $product->id, $combination->id) as $image) {
                $images[] = $link->getImageLink($product->link_rewrite, $image['id_image']);
            }

            //If no image for attribute, search default product image
            if (empty($images)) {
                foreach (Image::getImages($this->context->language->id, $product->id) as $image) {
                    $images[] = $link->getImageLink($product->link_rewrite, $image['id_image']);
                }
            }

            $oystProduct->__set('information', $informations);
            // $oystProduct->title = $title;
        } else {
            $title = is_array($product->name) ? reset($product->name) : $product->name;
            $reference = (string)$product->id;
            $oystProduct = new OystProduct($reference, $title, $oystPrice, $quantity);
            // $oystProduct->reference = (string)$product->id;
            $oystProduct->__set('ean', $product->ean13);
            $oystProduct->__set('weight', $product->weight);
            // $oystProduct->title = is_array($product->name) ? reset($product->name) : $product->name;
            // Stock
            $stockAvailable = new StockAvailable(StockAvailable::getStockAvailableIdByProductId($product->id));
            $oystProduct->__set('availableQuantity', $stockAvailable->quantity);
            // Images
            $images = array();
            foreach (Image::getImages($this->context->language->id, $product->id) as $image) {
                $images[] = $link->getImageLink($product->link_rewrite, $image['id_image']);
            }
        }

        // Common fields
        $oystProduct->__set('active', $product->active);
        $oystProduct->__set('materialized', ($product->is_virtual == '0' ? true : false));
        $oystProduct->__set('manufacturer', $product->manufacturer_name);
        $oystProduct->__set('size', $oystSize);
        $oystProduct->__set('condition', ($product->condition == 'used' ? 'reused' : $product->condition));
        $oystProduct->__set('categories', $categories);
        // $oystProduct->amountIncludingTax = $oystPrice;

        $oystProduct->url = $link->getProductLink($product);
        // $oystProduct->quantity = $quantity;

        if (empty($images)) {
            $images = array(Tools::getShopDomain(true) . '/modules/oyst/view/img/no_image.png');
        }

        $oystProduct->__set('images', $images);

        return $oystProduct;
    }

    public function reverseTransform($value)
    {
        // Implement this method if you need it
    }

    /**
     * @param Product $product
     * @param Combination $combination
     * @return OystProduct
     */
    public function transformCombination(Product $product, Combination $combination, $quantity = 1)
    {
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $link = new Link();
        } else {
            $link = $this->context->link;
        }
        $oystProductVariation = $this->transform($product);

        if ($oystProductVariation && $combination && $combination->id) {
            $oystPrice = new OystPrice($product->getPrice(true, $combination->id), $this->context->currency->iso_code);

            $oystProductVariation->__set('reference', (string)$combination->id);
            $oystProductVariation->__set('ean', $combination->ean13);
            $oystProductVariation->__set('weight', $combination->weight);

            $oystProductVariation->__set('amountIncludingTax', $oystPrice);
            $stockAvailable = new StockAvailable(StockAvailable::getStockAvailableIdByProductId($product->id, $combination->id));
            $oystProductVariation->__set('availableQuantity', $stockAvailable->quantity);
            $oystProductVariation->__set('quantity', $quantity);

            $images = array();
            foreach (Image::getImages($this->context->language->id, $product->id, $combination->id) as $image) {
                $images[] = $link->getImageLink($product->link_rewrite, $image['id_image']);
            }

            if (empty($images)) {
                foreach (Image::getImages($this->context->language->id, $product->id) as $image) {
                    $images[] = $link->getImageLink($product->link_rewrite, $image['id_image']);
                }
            }

            if (empty($images)) {
                $images = array(Tools::getShopDomain(true) . '/modules/oyst/view/img/no_image.png');
            }

            $oystProductVariation->__set('images', $images);

            $attributesInfo = $this->productRepository->getAttributesCombination($combination);
            $informations = array();
            foreach ($attributesInfo as $attributeInfo) {
                $informations[$attributeInfo['name']] = $attributeInfo['value'];
            }

            $oystProductVariation->__set('information', $informations);
        }

        return $oystProductVariation;
    }

    /**
     * @param ProductRepository $productRepository
     * @return ProductTransformer
     */
    public function setProductRepository($productRepository)
    {
        $this->productRepository = $productRepository;

        return $this;
    }
}
