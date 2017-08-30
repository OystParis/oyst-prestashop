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
        $state &= $this->createCarrier();
        $state &= $this->pushDefaultShipment();
        $state &= $this->createExportTable();
        $state &= $this->createOrderTable();
        $state &= $this->createShipmentTable();
        $state &= $this->createProductTable();
        $state &= $this->populateProductTable();

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
    public function createShipmentTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."oyst_shipment` (
                  `id_oyst_shipment` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `id_carrier_reference` int(10) unsigned NOT NULL,
                  `primary` tinyint(1) DEFAULT 0,
                  `type` varchar(128) NOT NULL,
                  `delay` int(10) unsigned NOT NULL,
                  `zones` varchar(128) NOT NULL,
                  `amount_leader` decimal(20,2) NOT NULL,
                  `amount_follower` decimal(20,2) NOT NULL,
                  `free_shipping` decimal(20,2) NULL,
                  `currency` varchar(16) DEFAULT NULL,
                  PRIMARY KEY (`id_oyst_shipment`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
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
    public function populateProductTable()
    {
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'id_product', 'ASC');
        $state = true;

        foreach($products as $product) {
            $state &= $this->db->insert(
                'oyst_product',
                array(
                    'id_product' => (int)$product['id_product'],
                    'active_oneclick' => 1,
                )
            );
        }

        return $state;
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

    public function uninstall()
    {
        $this->removeCarrier();
        $this->dropExportTable();
        $this->dropOrderTable();
        $this->dropShipmentTable();
        $this->dropProductTable();

        // Remove anything at the end
        $this->removeConfiguration();
    }

    public function createCarrier()
    {
        //Create new carrier
        $carrier = new Carrier(PSConfiguration::get(Configuration::ONE_CLICK_CARRIER));

        if (Validate::isLoadedObject($carrier)) {
            return true;
        }

        $carrier->name = 'Oyst One Click';
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->shipping_external = true;
        $carrier->range_behavior = 0;
        $carrier->is_free = false;
        $carrier->delay = array(
            PSConfiguration::get('PS_LANG_DEFAULT') => 'Delay'
        );
        $carrier->is_module = true;
        $carrier->external_module_name = $this->oyst->name;
        $carrier->need_range = true;

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                $this->db->insert('carrier_group', array(
                    'id_carrier' => (int)$carrier->id,
                    'id_group' => (int)$group['id_group']
                ));
            }

            $rangePrice = new RangePrice();
            $rangePrice->id_carrier = $carrier->id;
            $rangePrice->delimiter1 = '0';
            $rangePrice->delimiter2 = '1000000';
            $rangePrice->add();

            $rangeWeight = new RangeWeight();
            $rangeWeight->id_carrier = $carrier->id;
            $rangeWeight->delimiter1 = '0';
            $rangeWeight->delimiter2 = '1000000';
            $rangeWeight->add();

            $zones = Zone::getZones(true);
            foreach ($zones as $z) {
                $this->db->insert(
                    'carrier_zone',
                    array('id_carrier' => (int) $carrier->id, 'id_zone' => (int) $z['id_zone'])
                );

                $this->db->autoExecuteWithNullValues(
                    _DB_PREFIX_ . 'delivery',
                    array(
                        'id_carrier' => $carrier->id,
                        'id_range_price' => (int) $rangePrice->id,
                        'id_range_weight' => null,
                        'id_zone' => (int) $z['id_zone'],
                        'price' => '0'
                    ),
                    'INSERT'
                );

                $this->db->autoExecuteWithNullValues(
                    _DB_PREFIX_ . 'delivery',
                    array(
                        'id_carrier' => $carrier->id,
                        'id_range_price' => null,
                        'id_range_weight' => (int) $rangeWeight->id,
                        'id_zone' => (int) $z['id_zone'],
                        'price' => '0'
                    ),
                    'INSERT'
                );
            }

            PSConfiguration::updateValue(Configuration::ONE_CLICK_CARRIER, $carrier->id);
            return true;
        }
        return false;
    }

    private function removeConfiguration()
    {
        PSConfiguration::deleteByName(Configuration::ONE_CLICK_FEATURE_STATE);
        PSConfiguration::deleteByName(Configuration::ONE_CLICK_CARRIER);
        PSConfiguration::deleteByName(Configuration::CATALOG_EXPORT_STATE);
        PSConfiguration::deleteByName(Configuration::REQUESTED_CATALOG_DATE);
        PSConfiguration::deleteByName(Configuration::DISPLAY_ADMIN_INFO_STATE);
    }

    /**
     * @return bool
     */
    private function removeCarrier()
    {
        // Carrier should never be deleted because merchant needs to keep record for the orders information.
        $carrier = new Carrier(PSConfiguration::get(Configuration::ONE_CLICK_CARRIER));

        if (!Validate::isLoadedObject($carrier)) {
            return true;
        }

        $carrier->deleted = 1;
        return $carrier->save();
    }

    public function pushDefaultShipment()
    {
        $shipment = new OneClickShipment();

        $carrier = new OystCarrier(
            PSConfiguration::get(Configuration::ONE_CLICK_CARRIER),
            'Default and Free',
            OneClickShipment::HOME_DELIVERY
        );

        $amount = new ShipmentAmount(0, 0, 'EUR');
        $shipment
            ->setCarrier($carrier)
            ->setAmount($amount)
            ->setDelay(7)
            ->setFreeShipping(0)
            ->setPrimary(true)
            ->setZones(array('FR'))
        ;

        $shipmentService = AbstractShipmentServiceFactory::get($this->oyst, $this->oyst->getContext(), $this->db);
        $shipmentService->pushShipment($shipment);

        return $shipmentService->getRequester()->getApiClient()->getLastHttpCode() == "200";
    }
}
