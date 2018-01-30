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
            FROM "._DB_PREFIX_."attribute a
            INNER JOIN "._DB_PREFIX_."attribute_lang al ON (al.id_attribute = a.id_attribute AND al.id_lang = $langId)
            INNER JOIN "._DB_PREFIX_."attribute_group_lang agl ON (agl.id_attribute_group = a.id_attribute_group AND agl.id_lang = $langId)
            WHERE a.id_attribute IN ($queryWhereAttributes)
        ";

        return $this->db->executeS($query);
    }

    public function existActive($id_product)
    {
        return $this->db->getValue('
            SELECT COUNT(*)
            FROM '._DB_PREFIX_.'oyst_product
            WHERE id_product = '.(int) $id_product);
    }

    /**
     * For OneClick
     * @param $id_product
     * @return bool
     */
    public function getActive($id_product)
    {
        $active = $this->db->getValue('
            SELECT active_oneclick
            FROM '._DB_PREFIX_.'oyst_product
            WHERE id_product = '.(int) $id_product);

        if ($this->existActive($id_product) > 0) {
            if ($active == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * @param $id_product
     * @param $active
     * @return bool
     */
    public function setActive($id_product, $active = 1)
    {
        if ($this->existActive($id_product) == 0) {
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
