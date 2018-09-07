<?php

namespace Oyst\Classes\VersionCompliance;

use Oyst\Classes\Notification;
use Db;

class Helper
{
    public function saveObject($model, $status, $id_order = 0)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $update   = array(
                'order_id'   => $id_order,
                'status'     => $status,
                'date_upd'   => date('Y-m-d H:i:s'),
            );

            $where = '';

            if (is_int($model->id)) {
                $where = '`id_notification` = '.(int)$model->id;
            }

            return Db::getInstance()->update('oyst_notification', $update, $where);
        } else {
            $model->status = $status;
            $model->order_id = $id_order;

            return $model->save();
        }
    }
}
