<?php

namespace Oyst\Repository;

use Combination;
use Oyst;
use Product;

/**
 * Class Oyst\Repository\ProductRepository
 */
class ProductRepository extends AbstractOystRepository
{
    /**
     * @param int $limitProducts
     * @return array
     */
    public function getProductsNotExported($limitProducts = 2)
    {
        $limitProducts = (int)$limitProducts;
        $query = "
            SELECT 
              p.id_product,
              pa.id_product_attribute
            FROM ps_product p
            LEFT JOIN ps_product_attribute pa ON pa.id_product = p.id_product
            WHERE CONCAT(p.id_product, '-', IFNULL(pa.id_product_attribute, 0)) NOT IN (
              SELECT CONCAT(oec.productId, '-', IFNULL(oec.productAttributeId, 0))
              FROM ps_oyst_exported_catalog oec
            )
        ";

        if ($limitProducts > 0) {
            $query .= ' LIMIT ' . $limitProducts;
        }

        // Use replace to keep IDE working with database source on dev side.
        $products = $this->db->executeS(str_replace('ps_', _DB_PREFIX_, $query));

        return $products;
    }

    /**
     * @return int
     */
    public function getTotalProducts()
    {
        $query = "
            SELECT 
              count(1) totalProducts
            FROM ps_product p
            LEFT JOIN ps_product_attribute pa ON pa.id_product = p.id_product
        ";

        // Use replace to keep IDE working with database source on dev side.
        $totalProducts = $this->db->getValue(str_replace('ps_', _DB_PREFIX_, $query));
        return (int)$totalProducts;
    }

    /**
     * @param Oyst $oyst
     * @param array $baseProductToExport PrestaShop product
     * @param Oyst\Classes\OystProduct[] $oystProductsExported Oysts product really exported
     * @param $importId
     * @return bool
     */
    public function recordSentProductsFromOystProductList(Oyst $oyst, $baseProductToExport, $oystProductsExported, $importId)
    {
        $productIds = array();
        foreach ($baseProductToExport as $product) {
            $hasBeenExported = false;
            foreach ($oystProductsExported as $oystProduct) {
                $psProduct = new Product($product['id_product']);
                $combination = new Combination($product['id_product_attribute']);
                $baseReference = $oyst->getProductReference($psProduct, $combination);

                if ($baseReference == $oystProduct->getRef()) {
                    $hasBeenExported = true;
                    break;
                }
            }

            $productIds[] = array(
                'productId' => $product['id_product'],
                'productAttributeId' => (int)$product['id_product_attribute'],
                'importId' => $importId,
                'hasBeenExported' => (int) $hasBeenExported,
            );
        }
        return $this->db->insert('oyst_exported_catalog', $productIds);
    }

    /**
     * @param Product $product
     * @param Combination|null $combination
     * @return bool
     */
    public function recordSingleSentProduct(Product $product, Combination $combination = null)
    {
        return $this->db->insert('oyst_exported_catalog', array(
            'productId' => $product->id,
            'productAttributeId' => (int) $combination->id,
            'importId' => null,
            'hasBeenExported' => 1,
        ));
    }

    /**
     * @return array
     */
    public function getExportedProduct()
    {
        $query = '
            SELECT *
            FROM ps_oyst_exported_catalog
        ';

        $results = $this->db->executeS(str_replace('ps_', _DB_PREFIX_, $query));

        return $results;
    }

    /**
     * @return bool
     */
    public function truncateExportTable()
    {
        return $this->db->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'oyst_exported_catalog');
    }

    /**
     * @param Product $product
     * @param Combination $combination
     * @return mixed
     */
    public function isProductSent(Product $product, Combination $combination = null)
    {
        $productId = (int) $product->id;
        $combinationId = !$combination ? 0 : (int) $combination->id;

        $query = "
            SELECT *
            FROM ps_oyst_exported_catalog poec 
            WHERE  
              poec.productId = $productId
              AND poec.productAttributeId = $combinationId
        ";

        return $this->db->getValue(str_replace('ps_', _DB_PREFIX_, $query));
    }
}
