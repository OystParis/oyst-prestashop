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

use Combination;
use Exception;
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Product;
use Oyst\Transformer\ProductTransformer;
use Oyst\Classes\OystProduct;

/**
 * Class ProductService
 */
class ProductService extends AbstractOystService
{
    /** @var OystCatalogAPI */
    private $catalogApi;

    /** @var ProductTransformer */
    private $productTransformer;

    /** @var ProductRepository */
    private $productRepository;

    /**
     * @param OystCatalogAPI $catalogAPI
     * @return $this
     */
    public function setCatalogApi(OystCatalogAPI $catalogAPI)
    {
        $this->catalogApi = $catalogAPI;

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

    /**
     * @param Product $product
     * @return OystProduct
     * @throws Exception
     */
    public function getOystProduct(Product $product)
    {
        if (!$this->productTransformer instanceof ProductTransformer) {
            throw new Exception('Did you forget to set the Product Transformer ?');
        }

        // Flush cache because multiple call of this method could happens because of the 2 hooks (ProductSave and
        // CombinationSave (or Update)
        // It means this same process for product event will be executed and the query to get combinations will be
        // save to the cache, so we won't have the fresh info sent to the api as combination is not saved / created yet.
        // TODO: Try it with APC for example, for there is no way to flush the current cache
        //Cache::getInstance()->flush();

        $oystProduct = $this->productTransformer->transform($product);
        $combinations = Product::getProductAttributesIds($product->id);
        foreach ($combinations as $combinationInfo) {
            $combination = new Combination($combinationInfo['id_product_attribute']);
            $variation = $this->productTransformer->transformCombination($product, $combination);
            $oystProduct->addVariation($variation);
        }

        return $oystProduct;
    }

    /**
     * @param Product $product
     * @return bool
     * @throws Exception
     */
    public function sendProduct(Product $product)
    {
        $oystProduct = $this->getOystProduct($product);
        $response = $this->requester->call('postProduct', array($oystProduct));

        if ($this->requester->getApiClient()->getLastHttpCode() == 200 && isset($response['imported']) && 1 == $response['imported']) {
            $this->productRepository->recordOystProductSent($oystProduct);
            return true;
        }

        return false;
    }

    /**
     * @param OystProduct $product
     * @return bool
     */
    public function delete(OystProduct $product)
    {
        $this->requester->call('deleteProduct', array($product->getRef()));

        return $this->requester->getApiClient()->getLastHttpCode() == "200";
    }

    /**
     * @param ProductRepository $productRepository
     * @return ProductService
     */
    public function setProductRepository($productRepository)
    {
        $this->productRepository = $productRepository;
        return $this;
    }
}
