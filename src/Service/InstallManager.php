<?php

namespace Oyst\Service;

use Carrier;
use Db;
use Configuration as PSConfiguration;
use Zone;
use Group;
use Validate;

class InstallManager
{
    /**
     * @var string
     */
    private $tablePrefix;

    /**
     * @var Db
     */
    private $db;

    /**
     * @var \Oyst
     */
    private $oyst;

    public function __construct(Db $db, \Oyst $oyst, $tablePrefix = '')
    {
        $this->tablePrefix = $tablePrefix;
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
        $state &= $this->createExportTable();

        return $state;
    }

    /**
     * @return bool
     */
    public function createExportTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS ps_oyst_exported_catalog
            (
                productId INT,
                productAttributeId INT,
                importId VARCHAR(60),
                hasBeenExported TINYINT DEFAULT 0
            );  
        ";

        return $this->db->execute($this->prefixQuery($query));
    }

    /**
     * @return bool
     */
    public function dropExportTable()
    {
        $query = "
            DROP TABLE IF EXISTS ps_oyst_exported_catalog;
        ";

        return $this->db->execute($this->prefixQuery($query));
    }

    public function uninstall()
    {
        $this->removeCarrier();
        $this->dropExportTable();

        // Remove anything at the end
        $this->removeConfiguration();
    }

    /**
     * @param $query
     * @return string
     */
    private function prefixQuery($query)
    {
        return str_replace('ps_', $this->tablePrefix, $query);
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
        $carrier->is_module = false;
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
                Db::getInstance()->insert('carrier_zone',
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
}
