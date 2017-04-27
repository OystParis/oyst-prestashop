<?php

namespace Oyst\Service;

use Combination;
use Exception;
use Oyst\Api\OystCatalogApi;
use Oyst\Repository\ProductRepository;
use Product;
use Oyst\Transformer\ProductTransformer;
use Oyst\Classes\OystProduct;

/**
 * Class Oyst\Service\ProductService
 */
class ProductService extends AbstractOystService
{
    /** @var  OystCatalogAPI */
    private $catalogApi;

    /** @var  ProductTransformer */
    private $productTransformer;

    /** @var  ProductRepository */
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
     * @param Combination|null $combination
     * @return OystProduct
     * @throws Exception
     */
    public function getOystProduct(Product $product, Combination $combination = null)
    {
        if (!$this->productTransformer instanceof ProductTransformer) {
            throw new Exception('Did you forget to set the Product Transformer ?');
        }

        $oystProduct = $this->productTransformer->transformWithCombination($product, $combination);


        return $oystProduct;
    }

    /**
     * @param Product $product
     * @param Combination|null $combination
     * @return bool
     * @throws Exception
     */
    public function sendNewProduct(Product $product, Combination $combination = null)
    {
        if (!$this->catalogApi instanceof OystCatalogAPI) {
            throw new Exception('Oyst CatalogAPi is missing, did you forget to add it ?');
        }

        $oystProduct = $this->getOystProduct($product, $combination);
        $response = $this->requestApi($this->catalogApi, 'postProduct', $oystProduct);

        if ($this->catalogApi->getLastHttpCode() == 200 &&
            isset($response['imported']) &&
            1 == $response['imported']) {

            $this->productRepository->recordSentProducts(array(
                array(
                    'id_product' => $product->id,
                    'id_product_attribute' => $combination->id,
                )
            ), 0);
            return true;
        }

        return false;
    }

    /**
     * @param Product $product
     * @param Combination|null $combination
     * @return bool
     * @throws Exception
     */
    public function updateProduct(Product $product, Combination $combination = null)
    {
        if (!$this->catalogApi instanceof OystCatalogAPI) {
            throw new Exception('Oyst CatalogAPi is missing, did you forget to add it ?');
        }

        $oystProduct = $this->getOystProduct($product, $combination);
        // As Oyst create and update product with less error on postMethod, let's use it
        $this->requestApi($this->catalogApi, 'postProduct', $oystProduct);

        return $this->catalogApi->getLastHttpCode() == 200;
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
