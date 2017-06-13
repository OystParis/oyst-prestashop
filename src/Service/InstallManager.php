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

    public function uninstall()
    {
        $this->removeCarrier();
        $this->dropExportTable();
        $this->dropOrderTable();

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
        $carrier->range_behavior = 0;
        $carrier->is_free = true;
        $carrier->delay = array(
            PSConfiguration::get('PS_LANG_DEFAULT') => 'Delay'
        );
        $carrier->is_module = true;
        $carrier->external_module_name = $this->oyst->name;
        $carrier->need_range = false;

        if ($carrier->add()) {
            $groups = Group::getGroups(true);
            foreach ($groups as $group) {
                $this->db->insert('carrier_group', array(
                    'id_carrier' => (int)$carrier->id,
                    'id_group' => (int)$group['id_group']
                ));
            }

            $zones = Zone::getZones(true);
            foreach ($zones as $z) {
                Db::getInstance()->insert(
                    'carrier_zone',
                    array('id_carrier' => (int) $carrier->id, 'id_zone' => (int) $z['id_zone'])
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
        $result = $shipmentService->pushShipment($shipment);
    }
}
