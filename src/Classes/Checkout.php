<?php

namespace Oyst\Classes;

use Db;

class Checkout
{
    /**
     * @param int $id_cart
     * @return string
     */
    public static function getIdOystFromIdCart($id_cart)
    {
        $res = Db::getInstance()->getValue("SELECT `oyst_cart_id` 
            FROM `"._DB_PREFIX_."oyst_checkout` 
            WHERE `id_cart` = '".$id_cart."'");
        return (empty($res) ? '' : $res);
    }

    /**
     * @param string $oyst_cart_id
     * @return int
     */
    public static function getIdCartFromIdCartOyst($oyst_cart_id)
    {
        return (int)Db::getInstance()->getValue("SELECT `id_cart` 
            FROM `"._DB_PREFIX_."oyst_checkout` 
            WHERE `oyst_cart_id` = '".$oyst_cart_id."'");
    }
}
