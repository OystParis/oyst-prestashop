<?php

namespace Oyst\Classes;

use Db;
use Exception;
use ObjectModel;

class Notification extends ObjectModel
{
    public $id_notification;
    public $oyst_id;
    public $cart_id;
    public $order_id;
    public $status;
    public $date_add;
    public $date_upd;

    const START_STATUS = 'start';
    const END_STATUS = 'finished';
    const WAITING_STATUS = 'waiting';

    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'oyst_notification',
        'primary' => 'id_notification',
        'fields' => array(
            'oyst_id' =>    array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'cart_id' =>    array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'order_id' =>   array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'status' =>     array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'date_add' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' =>   array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * @return bool
     */
    public function start()
    {
        $this->status = self::START_STATUS;

        try {
            $res = $this->save();
        } catch (Exception $e) {
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
            echo json_encode(array('error' => print_r($e->getMessage())));
            exit;
        }
        return $res;
    }

    /**
     * @param $id_order
     * @return bool
     */
    public function complete($id_order)
    {
        $this->status = self::END_STATUS;
        $this->order_id = $id_order;
        try {
            $res = $this->save();
        } catch (Exception $e) {
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
            echo json_encode(array('error' => print_r($e->getMessage())));
            exit;
        }
        return $res;
    }

    /**
     * @return bool
     */
    public function isAlreadyStarted()
    {
        return !empty($this->status) && $this->status == self::START_STATUS;
    }

    /**
     * @return bool
     */
    public function isAlreadyFinished()
    {
        return !empty($this->status) && $this->status == self::END_STATUS;
    }

    public static function cartLinkIsAlreadyDone($id_cart)
    {
        return (self::getOystIdByCartId($id_cart) !== '');
    }

    /**
     * @param $oyst_id
     * @return null|Notification
     */
    public static function getNotificationByOystId($oyst_id)
    {
        $id_notification = Db::getInstance()->getValue("SELECT `id_notification` 
            FROM `"._DB_PREFIX_."oyst_notification` 
            WHERE `oyst_id` = '".$oyst_id."' 
            ORDER BY `id_notification` DESC");

        $notification = null;
        if (empty($id_notification)) {
            $notification = new Notification();
            $notification->oyst_id = $oyst_id;
        } else {
            $notification = new Notification($id_notification);
        }
        return $notification;
    }

    /**
     * @param $id_order
     * @return string
     */
    public static function getOystIdByOrderId($id_order)
    {
        $oyst_id = Db::getInstance()->getValue("SELECT `oyst_id` 
            FROM `"._DB_PREFIX_."oyst_notification` 
            WHERE `order_id` = ".$id_order." 
            ORDER BY `id_notification` DESC");

        if (!empty($oyst_id)) {
            return $oyst_id;
        } else {
            return '';
        }
    }

    /**
     * @param $id_cart
     * @return string
     */
    public static function getOystIdByCartId($id_cart)
    {
        $oyst_id = Db::getInstance()->getValue("SELECT `oyst_id` 
            FROM `"._DB_PREFIX_."oyst_notification` 
            WHERE `cart_id` = ".$id_cart." 
            ORDER BY `id_notification` DESC");

        if (!empty($oyst_id)) {
            return $oyst_id;
        } else {
            return '';
        }
    }

    /**
     * @param $id_oyst
     * @return int
     */
    public static function getOrderIdByOystId($id_oyst)
    {
        $order_id = Db::getInstance()->getValue("SELECT `order_id` 
            FROM `"._DB_PREFIX_."oyst_notification` 
            WHERE `oyst_id` = '".$id_oyst."' 
            ORDER BY `id_notification` DESC");

        if (!empty($order_id)) {
            return $order_id;
        } else {
            return 0;
        }
    }

    /**
     * @param $id_oyst
     * @return int
     */
    public static function getCartIdByOystId($id_oyst)
    {
        $cart_id = Db::getInstance()->getValue("SELECT `cart_id` 
            FROM `"._DB_PREFIX_."oyst_notification` 
            WHERE `oyst_id` = '".$id_oyst."' 
            ORDER BY `id_notification` DESC");

        if (!empty($cart_id)) {
            return $cart_id;
        } else {
            return 0;
        }
    }
}
