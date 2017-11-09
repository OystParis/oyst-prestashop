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

/*
 * Security
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystPaymentNotification extends ObjectModel
{
    const EVENT_AUTHORISATION = 'AUTHORISATION';
    const EVENT_FRAUD_VALIDATION = 'FRAUD_VALIDATION';
    const EVENT_CAPTURE = 'CAPTURE';
    const EVENT_CANCELLATION = 'CANCELLATION';
    const EVENT_REFUND = 'REFUND';

    public $id;

    /** @var integer Order ID */
    public $id_order;

    /** @var integer Cart ID */
    public $id_cart;

    /** @var string Payment ID */
    public $payment_id;

    /** @var string Event Code */
    public $event_code;

    /** @var string Event Data */
    public $event_data;

    /** @var string Date Event */
    public $date_event;

    /** @var string Date Import */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'oyst_payment_notification',
        'primary' => 'id_oyst_payment_notification',
        'multilang' => false,
        'fields' => array(
            'id_order' => array('type' => TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_cart' => array('type' => TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'payment_id' => array('type' => TYPE_STRING, 'required' => true, 'size' => 128),
            'event_code' => array('type' => TYPE_STRING, 'required' => true, 'size' => 128),
            'event_data' => array('type' => TYPE_STRING, 'required' => true, 'size' => 128),
            'date_event' => array('type' => TYPE_DATE, 'validate' => 'isDate'),
            'date_add' => array('type' => TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    /**
     * Retrieve OystPaymentNotification object from Cart ID
     * @param $id_cart
     */
    public static function getOystPaymentNotificationFromCartId($id_cart)
    {
        $id_oyst_notification = Db::getInstance()->getValue('
        SELECT `id_oyst_payment_notification`
        FROM `'._DB_PREFIX_.'oyst_payment_notification`
        WHERE `id_cart` = '.(int)$id_cart);

        return new OystPaymentNotification($id_oyst_notification);
    }

    public static function existEventCode($id_cart, $event_code)
    {
        $sql = 'SELECT COUNT(*)
        FROM `'._DB_PREFIX_.'oyst_payment_notification`
        WHERE `id_cart` ='.(int)$id_cart.'
        AND event_code = "'.$event_code.'"';

        $exist = Db::getInstance()->getValue($sql);
        if ($exist > 0) {
            return true;
        } else {
            return false;
        }
    }
}
