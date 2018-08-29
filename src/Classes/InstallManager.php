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

namespace Oyst\Classes;

use Db;
use Configuration;
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
        return $state;
    }

    public function uninstall()
    {
        $this->dropNotificationTable();
        $this->dropCustomerTable();
        $this->removeConfiguration();
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
        Configuration::deleteByName('OYST_SCRIPT_TAG_URL');
    }
}
