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

namespace Oyst\Repository;

use Combination;
use Oyst;
use Product;

/**
 * Class ProductRepository
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
            FROM "._DB_PREFIX_."product p
            LEFT JOIN "._DB_PREFIX_."product_attribute pa ON pa.id_product = p.id_product
            WHERE CONCAT(p.id_product, '-', IFNULL(pa.id_product_attribute, 0)) NOT IN (
              SELECT CONCAT(oec.productId, '-', IFNULL(oec.productAttributeId, 0))
              FROM "._DB_PREFIX_."oyst_exported_catalog oec
            )
        ";

        if ($limitProducts > 0) {
            $query .= ' LIMIT ' . $limitProducts;
        }

        $products = $this->db->executeS($query);

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
            FROM "._DB_PREFIX_."product p
            LEFT JOIN "._DB_PREFIX_."product_attribute pa ON pa.id_product = p.id_product
        ";

        // Use replace to keep IDE working with database source on dev side.
        $totalProducts = $this->db->getValue($query);
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
            FROM '._DB_PREFIX_.'oyst_exported_catalog
        ';

        $results = $this->db->executeS($query);

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
            FROM "._DB_PREFIX_."oyst_exported_catalog poec
            WHERE  
              poec.productId = $productId
              AND poec.productAttributeId = $combinationId
        ";

        return $this->db->getValue($query);
    }
}
