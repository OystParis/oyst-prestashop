<?php

namespace Oyst\Classes;

use Db;
use Exception;
use ObjectModel;

class Notification extends ObjectModel
{
    public $id_notification;
    public $oyst_order_id;
    public $order_id;
    public $status;
    public $date_add;
    public $date_upd;

    const START_STATUS = 'start';
    const END_STATUS = 'finished';

    public static $definition = array(
        'table' => 'oyst_notification',
        'primary' => 'id_notification',
        'fields' => array(
            'oyst_order_id' =>  array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'order_id' =>       array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'status' =>         array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

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

    public function isAlreadyStarted()
    {
        return !empty($this->status) && $this->status == self::START_STATUS;
    }

    public function isAlreadyFinished()
    {
        return !empty($this->status) && $this->status == self::END_STATUS;
    }

    public static function getNotificationByOystOrderId($oyst_order_id)
    {
        $id_notification = Db::getInstance()->getValue("SELECT `id_notification` 
            FROM `"._DB_PREFIX_."oyst_notification` 
            WHERE `oyst_order_id` = '".$oyst_order_id."' 
            ORDER BY `id_notification` DESC");

        $notification = null;
        if (empty($id_notification)) {
            $notification = new Notification();
            $notification->oyst_order_id = $oyst_order_id;
        } else {
            $notification = new Notification($id_notification);
        }
        return $notification;
    }
}
