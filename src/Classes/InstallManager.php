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
        $state &= $this->createNotificationTable();
        $state &= $this->installOrderStates();

        //Generate API key if not exists
        if (!Configuration::hasKey(OystAPIKey::CONFIG_KEY)) {
            $state &= OystAPIKey::generateAPIKey();
        }

        return $state;
    }

    /**
     * @return bool
     */
    public function installOrderStates()
    {
        $result = true;
        $langId = Configuration::get('PS_LANG_DEFAULT');
        $orderState = new OrderState(Configuration::get('OYST_STATUS_CANCELLATION_PENDING'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Annulation en cours',
            );
            $orderState->color = '#FFF168';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_CANCELLATION_PENDING', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_REFUND_PENDING'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Remboursement en cours',
            );
            $orderState->color = '#FFF168';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_REFUND_PENDING', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_PARTIAL_REFUND_PEND'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Remboursement partiel en cours',
            );
            $orderState->color = '#FFF168';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_PARTIAL_REFUND_PEND', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_PARTIAL_REFUND'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Remboursement partiel',
            );
            $orderState->color = '#FF7F50';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_PARTIAL_REFUND', $orderState->id);
        }
        $orderState = new OrderState(Configuration::get('OYST_STATUS_FRAUD_CHECK'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'En attente de vérification fraude par Oyst',
            );
            $orderState->color = '#FF8C00';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_FRAUD_CHECK', $orderState->id);
        }
        $orderState = new OrderState(Configuration::get('OYST_STATUS_WAIT_PAYMENT'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'En attente de paiement chez Oyst',
            );
            $orderState->color = '#360088';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_WAIT_PAYMENT', $orderState->id);
        }

        $orderState = new OrderState(Configuration::get('OYST_STATUS_FRAUD'));

        if (!Validate::isLoadedObject($orderState)) {
            $orderState->name = array(
                $langId => 'Paiement frauduleux - NE PAS EXPEDIER',
            );
            $orderState->color = '#980000';
            $orderState->unremovable = true;
            $orderState->deleted = false;
            $orderState->delivery = false;
            $orderState->invoice = false;
            $orderState->logable = false;
            $orderState->module_name = $this->name;
            $orderState->paid = false;
            $orderState->hidden = false;
            $orderState->shipped = false;
            $orderState->send_email = false;

            $result &= $orderState->add();

            Configuration::updateValue('OYST_STATUS_FRAUD', $orderState->id);
        }

        return $result;
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

        Configuration::deleteByName(OystAPIKey::CONFIG_KEY);
        Configuration::deleteByName('OYST_SCRIPT_TAG_URL');
    }
}
