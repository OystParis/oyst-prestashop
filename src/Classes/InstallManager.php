<?php

namespace Oyst\Classes;

use Db;
use Configuration;
use Oyst\Services\OystStatusService;
use Tools;

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
        $state &= $this->createNotificationTable();
        $state &= $this->createCustomerTable();
        $state &= $this->updateConstants();

        //Generate API key if not exists
        if (!Configuration::hasKey(OystAPIKey::CONFIG_KEY)) {
            $state &= OystAPIKey::generateAPIKey();
        }

        if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false) {
            //Check if HTTP_AUTHORIZATION is catchable in PHP
            if (!Configuration::get('PS_WEBSERVICE_CGI_HOST')) {
                $fields = array(
                    'ajax' => 1,
                    'action' => 'check_http_authorization'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, Tools::getShopDomainSsl(true).'/module/oyst/ajax?'.http_build_query($fields));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: test'));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = json_decode(curl_exec($ch), true);
                curl_close($ch);
                if (!$response['http_authorization']) {
                    Configuration::updateValue('PS_WEBSERVICE_CGI_HOST', 1);
                    Tools::generateHtaccess();
                }
            }
        }

        $state &= OystStatusService::getInstance()->createAllStatus();

        return $state;
    }

    public function uninstall()
    {
        $state = true;
        $state &= $this->dropNotificationTable();
        $state &= $this->dropCustomerTable();
        $state &= $this->removeConfiguration();

        return $state;
    }

    public function updateConstants()
    {
        $state = true;
        //Generate API key if not exists
        if (!Configuration::hasKey(OystAPIKey::CONFIG_KEY)) {
            $state &= OystAPIKey::generateAPIKey();
        }
        return $state;
    }

    /**
     * @return bool
     */
    public function createNotificationTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."oyst_notification` (
              `id_notification` int(11) NOT NULL AUTO_INCREMENT,
              `oyst_id` varchar(255) NOT NULL,
              `cart_id` int(11) DEFAULT NULL,
              `order_id` int(11) DEFAULT NULL,
              `status` varchar(255) NOT NULL,
              `order_email_data` TEXT DEFAULT NULL,
              `date_add` datetime NOT NULL,
              `date_upd` datetime NOT NULL,
              PRIMARY KEY (`id_notification`)
            ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4;
        ";

        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function createCustomerTable()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."oyst_customer` (
                `id_customer` int(11) NOT NULL,
                `oyst_customer_id` int(11) NOT NULL,
                PRIMARY KEY (`id_customer`,`oyst_customer_id`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4;
        ";

        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function dropNotificationTable()
    {
        $query = "
            DROP TABLE IF EXISTS "._DB_PREFIX_."oyst_notification;
        ";

        return $this->db->execute($query);
    }

    /**
     * @return bool
     */
    public function dropCustomerTable()
    {
        $query = "
            DROP TABLE IF EXISTS "._DB_PREFIX_."oyst_customer;
        ";

        return $this->db->execute($query);
    }

    private function removeConfiguration()
    {
        return Configuration::deleteByName('OYST_SCRIPT_TAG_URL');
    }
}
