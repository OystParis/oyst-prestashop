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
use Oyst\Classes\OystProduct;
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
            ORDER BY p.id_product, pa.id_product_attribute
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
     * @param OystProduct[] $oystProductsExported Oysts product really exported
     * @param $importId
     * @return bool
     */
    public function recordSentProductsFromOystProductList(Oyst $oyst, $baseProductToExport, $oystProductsExported, $importId)
    {
        $productIds = array();
        foreach ($baseProductToExport as $product) {
            $hasBeenExported = false;
            foreach ($oystProductsExported as $oystProduct) {
                if ($product['id_product'] == $oystProduct->getRef()) {
                    // No variation possible
                    if (!$product['id_product_attribute']) {
                        $hasBeenExported = true;
                        break;
                    } else {
                        // Check out if the variation has been sent
                        foreach ($oystProduct->getVariations() as $variationProduct) {
                            if ($product['id_product_attribute'] == $variationProduct->getRef()) {
                                $hasBeenExported = true;
                                break;
                            }
                        }
                    }
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
     * @param OystProduct $product
     * @return bool
     */
    public function recordOystProductSent(OystProduct $product)
    {
        $this->db->delete('oyst_exported_catalog', 'productId = '.(int) $product->getRef());

        $variations = $product->getVariations();
        $succeed = true;
        if (count($variations)) {
            foreach ($variations as $variation) {
                $succeed &= $this->db->insert('oyst_exported_catalog', array(
                    'productId' => $product->getRef(),
                    'productAttributeId' => $variation->getRef(),
                    'importId' => null,
                    'hasBeenExported' => 1,
                ));
            }
        } else {
            $succeed = $this->db->insert('oyst_exported_catalog', array(
                'productId' => $product->getRef(),
                'productAttributeId' => 0,
                'importId' => null,
                'hasBeenExported' => 1,
            ));
        }

        return $succeed;
    }

    /**
     * @return array
     */
    public function getExportedProduct()
    {
        $query = '
            SELECT productId, productAttributeId, importId, hasBeenExported
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
            SELECT productId, productAttributeId, importId, hasBeenExported
            FROM "._DB_PREFIX_."oyst_exported_catalog poec
            WHERE
              poec.productId = $productId
        ";

        $products = $this->db->executeS($query);

        $isSent = count($products);
        if ($isSent && $combinationId) {
            $combinationFound = false;
            foreach ($products as $productInfo) {
                if ($productInfo['productAttributeId'] == $combinationId) {
                    $combinationFound = true;
                    break;
                }
            }
            $isSent = $combinationFound;
        }

        return $isSent;
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getExportedFromProduct(Product $product)
    {
        $productId = (int) $product->id;

        $query = "
            SELECT productId, productAttributeId, importId, hasBeenExported
            FROM "._DB_PREFIX_."oyst_exported_catalog poec
            WHERE
              poec.productId = $productId
              AND hasBeenExported = 1
        ";

        return $this->db->executeS($query);
    }

    /**
     * @param Combination $combination
     * @return array
     */
    public function getAttributesCombination(Combination $combination)
    {
        $langId = \Configuration::get('PS_LANG_DEFAULT');
        $attributes = $combination->getAttributesName($langId);
        $attributesId = array();

        foreach ($attributes as $attributeInfo) {
            $attributesId[] = $attributeInfo['id_attribute'];
        }

        $queryWhereAttributes = rtrim(implode(', ', $attributesId), ',');

        $query = "
            SELECT agl.public_name as name, al.name as value
            FROM ps_attribute a
            INNER JOIN ps_attribute_lang al ON (al.id_attribute = a.id_attribute AND al.id_lang = $langId)
            INNER JOIN ps_attribute_group_lang agl ON (agl.id_attribute_group = a.id_attribute_group AND agl.id_lang = $langId)
            WHERE a.id_attribute IN ($queryWhereAttributes)
        ";

        return $this->db->executeS($query);
    }

    /**
     * For OneClick
     * @param $id_product
     * @return bool
     */
    public function getActive($id_product)
    {
        return $this->db->getValue('
            SELECT active_oneclick
            FROM '._DB_PREFIX_.'oyst_product
            WHERE id_product = '.(int) $id_product);
    }

    /**
     * @param $id_product
     * @param $active
     * @return bool
     */
    public function setActive($id_product, $active = 1)
    {
        $state = $this->getActive($id_product);

        if ($state === false) {
            return $this->db->insert(
                'oyst_product',
                array(
                    'id_product' => (int)$id_product,
                    'active_oneclick' => (bool)$active
                )
            );
        } else {
            return $this->db->update(
                'oyst_product',
                array(
                    'active_oneclick' => (bool) $active,
                ),
                'id_product = '.(int)$id_product
            );
        }
    }
}
