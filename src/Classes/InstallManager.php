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
use OrderState;
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
        $state &= $this->updateConstants();

        //Generate API key if not exists
        if (!Configuration::hasKey(OystAPIKey::CONFIG_KEY)) {
            $state &= OystAPIKey::generateAPIKey();
        }

        //Check if HTTP_AUTHORIZATION is catchable in PHP
        if (!Configuration::get('PS_WEBSERVICE_CGI_HOST')) {
            $fields = array(
                'ajax' => 1,
                'action' => 'check_http_authorization'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, Tools::getShopDomainSsl(true).'/module/oyst/ajax?'.http_build_query($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: test'
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if (!$response['http_authorization']) {
                Configuration::updateValue('PS_WEBSERVICE_CGI_HOST', 1);
                Tools::generateHtaccess();
            }
        }
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
              `oyst_order_id` varchar(255) NOT NULL,
              `order_id` int(11) DEFAULT NULL,
              `status` varchar(255) NOT NULL,
              `date_add` datetime NOT NULL,
              `date_upd` datetime NOT NULL,
              PRIMARY KEY (`id_notification`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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

    public function uninstall()
    {
        $this->dropNotificationTable();
        // Remove anything at the end
        $this->removeConfiguration();
    }

    private function removeConfiguration()
    {
        $orderState = new OrderState(Configuration::get('OYST_STATUS_CANCELLATION_PENDING'));
        $orderState->delete();
        $orderState = new OrderState(Configuration::get('OYST_STATUS_REFUND_PENDING'));
        $orderState->delete();
        $orderState = new OrderState(Configuration::get('OYST_STATUS_PARTIAL_REFUND'));
        $orderState->delete();
        $orderState = new OrderState(Configuration::get('OYST_STATUS_PARTIAL_REFUND_PEND'));
        $orderState->delete();
        $orderState = new OrderState(Configuration::get('OYST_STATUS_FRAUD_CHECK'));
        $orderState->delete();
        $orderState = new OrderState(Configuration::get('OYST_STATUS_WAIT_PAYMENT'));
        $orderState->delete();
        $orderState = new OrderState(Configuration::get('OYST_STATUS_FRAUD'));
        $orderState->delete();

        // State Oyst
        Configuration::deleteByName('OYST_STATUS_CANCELLATION_PENDING');
        Configuration::deleteByName('OYST_STATUS_REFUND_PENDING');
        Configuration::deleteByName('OYST_STATUS_PARTIAL_REFUND');
        Configuration::deleteByName('OYST_STATUS_PARTIAL_REFUND_PEND');
        Configuration::deleteByName('OYST_STATUS_FRAUD_CHECK');
        Configuration::deleteByName('OYST_STATUS_WAIT_PAYMENT');
        Configuration::deleteByName('OYST_STATUS_FRAUD');

        Configuration::deleteByName('OYST_SCRIPT_TAG_URL');
    }
}
