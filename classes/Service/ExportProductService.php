<?php

class ExportProductService
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /** @var  string */
    private $weightUnit;

    /** @var  string */
    private $dimensionUnit;

    /**
     * @var Oyst
     */
    private $oyst;

    /** @var  OystCatalogAPI */
    private $oystCatalogAPI;

    /**
     * ExportProductService constructor.
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param Oyst $oyst
     */
    public function __construct(Context $context, ProductRepository $productRepository, Oyst $oyst)
    {
        $this->context = $context;
        $this->productRepository = $productRepository;
        $this->weightUnit = 'kg';
        $this->dimensionUnit = 'cm';
        $this->oyst = $oyst;
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
     */
    public function setCatalogApi(OystCatalogAPI $oystCatalogAPI)
    {
        $this->oystCatalogAPI = $oystCatalogAPI;
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
                if ($productInfo['id_product_attribute']) {
                    $combination = new Combination($productInfo['id_product_attribute']);
                }
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

            // Combination fields
            $oystProduct->setRef($product->id.($combination->id ? '-'.$combination->id : 0));
            $oystProduct->setEan(Validate::isLoadedObject($combination) ? $combination->ean13 : $product->ean13);
            $oystProduct->setWeight((Validate::isLoadedObject($combination) ? $combination->weight : $product->weight).$this->weightUnit);

            // Common fields
            $oystProduct->setActive($product->active);
            $oystProduct->setManufacturer($product->manufacturer_name);
            $oystProduct->setSize([
                'depth' => (int) $product->depth,
                'width' => (int) $product->width,
                'height' => (int) $product->height,
            ]);

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
                $images = ['https://www.google.com/logos/doodles/2015/googles-new-logo-5078286822539264.3-hp2x.gif'];
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
        $prestaShopProducts = $this->productRepository->getProductsNotExported();
        $products = $this->transformProducts($prestaShopProducts);
        $this->oystCatalogAPI->postProducts($products);
        $state = false;

        if ($this->oystCatalogAPI->getLastHttpCode() == 200) {
            $this->productRepository->recordSentProducts($prestaShopProducts, $importId);
        }

        return $state;
    }
}
