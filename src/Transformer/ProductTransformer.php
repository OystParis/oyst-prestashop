<?php

namespace Oyst\Transformer;

use Combination;
use Context;
use Image;
use Oyst\Classes\OystCategory;
use Oyst\Classes\OystPrice;
use Oyst\Classes\OystProduct;
use Oyst\Classes\OystSize;
use Product;
use StockAvailable;
use Tools;

/**
 * Class Oyst\Transformer\ProductTransformer
 */
class ProductTransformer extends AbstractTransformer
{
    /**
     * ProductTransformer constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param Product $product
     *
     * @return OystProduct
     */
    public function transform($product)
    {
        $oystProduct = new OystProduct();

        $oystPrice = new OystPrice($product->getPrice(true), $this->context->currency->iso_code);

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
        $oystProduct->setRef($product->id);
        $oystProduct->setEan($product->ean13);
        $oystProduct->setWeight($product->weight);

        // Common fields
        $oystProduct->setActive($product->active);
        $oystProduct->setManufacturer($product->manufacturer_name);
        $oystProduct->setSize($oystSize);
        $oystProduct->setCondition(($product->condition == 'used' ? 'reused' : $product->condition));
        $oystProduct->setCategories($categories[$product->id]);
        $oystProduct->setAmountIncludingTax($oystPrice);
        $oystProduct->setAvailableQuantity(StockAvailable::getStockAvailableIdByProductId($product->id));
        $oystProduct->setTitle(is_array($product->name) ? reset($product->name) : $product->name);
        $oystProduct->setDescription(is_array($product->description) ? reset($product->description) : $product->description);
        $oystProduct->setShortDescription(is_array($product->description_short) ? reset($product->description_short) : $product->description_short);
        $oystProduct->setUrl($this->context->link->getProductLink($product));

        $images = [];
        foreach (Image::getImages($this->context->language->id, $product->id) as $image) {
            $images[] = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
        }

        if (empty($images)) {
            $images = [Tools::getShopDomain(true) . '/modules/oyst/view/img/no_image.png'];
        }

        $oystProduct->setImages($images);

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
    public function transformWithCombination(Product $product, Combination $combination)
    {
        $oystProduct = $this->transform($product);

        if ($combination && $combination->id) {

            $oystPrice = new OystPrice($product->getPrice(true, $combination->id), $this->context->currency->iso_code);

            $oystProduct->setRef($product->id.'-'.$combination->id);
            $oystProduct->setEan($combination->ean13);
            $oystProduct->setWeight($combination->weight);

            $oystProduct->setAmountIncludingTax($oystPrice);
            $oystProduct->setAvailableQuantity(StockAvailable::getStockAvailableIdByProductId($product->id, $combination->id));

            $images = [];
            foreach (Image::getImages($this->context->language->id, $product->id, $combination->id) as $image) {
                $images[] = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
            }

            if (empty($images)) {
                $images = [Tools::getShopDomain(true) . '/modules/oyst/view/img/no_image.png'];
            }

            $oystProduct->setImages($images);
        }

        return $oystProduct;
    }
}
