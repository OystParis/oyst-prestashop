<?php

namespace Oyst\Controller\VersionCompliance;

use Db;
use Oyst\Classes\Notification;

class Helper
{
    public function saveNotification($oyst_id, $cart_id, $status)
    {
        if (Notification::cartLinkIsAlreadyDone($cart_id)) {
            $notification = Notification::getNotificationByCartId($cart_id);
            if ($notification->oyst_id != $oyst_id) {
                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $update = array(
                        'oyst_id' => $oyst_id,
                        'date_upd'   => date('Y-m-d H:i:s'),
                    );
                    return Db::getInstance()->update('oyst_notification', $update, 'id_notification = '.$notification->id);
                } else {
                    $notification->oyst_id = $oyst_id;
                    return $notification->update();
                }
            }
        } else {
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                $insert   = array(
                    'oyst_id'    => $oyst_id,
                    'cart_id'    => $cart_id,
                    'order_id'   => 0,
                    'status'     => $status,
                    'date_add'   => date('Y-m-d H:i:s'),
                    'date_upd'   => date('Y-m-d H:i:s'),
                );

                return Db::getInstance()->insert('oyst_notification', $insert);
            } else {
                $notification = new Notification();
                $notification->cart_id = $cart_id;
                $notification->oyst_id = $oyst_id;
                $notification->status = $status;
                return $notification->save();
            }
        }
        return false;
    }
}
