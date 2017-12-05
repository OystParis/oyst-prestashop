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

use Carrier;
use Product;
use Context;
use RangePrice;
use RangeWeight;
use Db;
use Configuration as PSConfiguration;
use Oyst\Classes\OneClickShipment;
use Oyst\Classes\OystCarrier;
use Oyst\Classes\ShipmentAmount;
use Oyst\Factory\AbstractShipmentServiceFactory;
use Zone;
use Group;
use Validate;

class InstallManager
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @var \Oyst
     */
    private $oyst;

    public function __construct(Db $db, \Oyst $oyst)
    {
        $this->db = $db;
        $this->oyst = $oyst;
    }

    /**
     * @return bool
     */
    public function install()
    {
        $state = true;
        $state &= $this->createExportTable();
        $state &= $this->createOrderTable();
        $state &= $this->createProductTable();

        return $state;
    }

    /**
     * @return bool
     */
    public function createExportTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."oyst_exported_catalog
            (
                productId INT,
                productAttributeId INT,
                importId VARCHAR(60),
                hasBeenExported TINYINT DEFAULT 0
            );
        ";

        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function createOrderTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."oyst_api_order` (
                  `orderId` int(11) DEFAULT NULL,
                  `orderGUID` varchar(64) CHARACTER SET latin1 DEFAULT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function createProductTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."oyst_product` (
                `id_product` int(11) unsigned NOT NULL,
                `active_oneclick` tinyint(1) NOT NULL DEFAULT 1
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        return $this->db->execute($query);
    }

    /**
    * @return bool
    */
    public function dropExportTable()
    {
        $query = "
            DROP TABLE IF EXISTS "._DB_PREFIX_."oyst_exported_catalog;
        ";

        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function dropOrderTable()
    {
        $query = "
            DROP TABLE IF EXISTS "._DB_PREFIX_."oyst_api_order;
        ";

        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function dropShipmentTable()
    {
        $query = "
            DROP TABLE IF EXISTS "._DB_PREFIX_."oyst_shipment;
        ";
        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function dropProductTable()
    {
        $query = "
            DROP TABLE IF EXISTS "._DB_PREFIX_."oyst_product;
        ";

        return $this->db->execute($query);
    }

    public function truncateProductTable()
    {
        $query = "TRUNCATE "._DB_PREFIX_."oyst_product";
        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function disableProductTable()
    {
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'id_product', 'ASC');
        $state = false;

        foreach ($products as $product) {
            $state &= $this->db->insert(
                'oyst_product',
                array(
                    'id_product' => (int)$product['id_product'],
                    'active_oneclick' => 0,
                )
            );
        }

        return $state;
    }

    public function uninstall()
    {
        $this->dropExportTable();
        $this->dropOrderTable();
        $this->dropShipmentTable();
        $this->dropProductTable();

        // Remove anything at the end
        $this->removeConfiguration();
    }

    private function removeConfiguration()
    {
        PSConfiguration::deleteByName(Configuration::ONE_CLICK_FEATURE_STATE);
        PSConfiguration::deleteByName(Configuration::CATALOG_EXPORT_STATE);
        PSConfiguration::deleteByName(Configuration::REQUESTED_CATALOG_DATE);
        PSConfiguration::deleteByName(Configuration::DISPLAY_ADMIN_INFO_STATE);
        PSConfiguration::deleteByName('FC_OYST_THEME_BTN');
        PSConfiguration::deleteByName('FC_OYST_COLOR_BTN');
        PSConfiguration::deleteByName('FC_OYST_WIDTH_BTN');
        PSConfiguration::deleteByName('FC_OYST_HEIGHT_BTN');
        PSConfiguration::deleteByName('FC_OYST_POSITION_BTN');
    }
}
