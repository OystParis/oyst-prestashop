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

class OystShipment extends ObjectModel
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $id_carrier;

    /**
     * @var bool
     */
    public $primary;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $zones;

    /**
     * @var float
     */
    public $amount_leader;

    /**
     * @var float
     */
    public $amount_follower;

    /**
     * @var string
     */
    public $amount_currency;

    /**
     * @var bool
     */
    public $free_shipping;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'oyst_shipment',
        'primary' => 'id_oyst_shipment',
        'multilang' => false,
        'fields' => array(
            'id_carrier' => array('type' => TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'primary' => array('type' => TYPE_BOOL, 'required' => true, 'size' => 1),
            'type' => array('type' => TYPE_STRING, 'required' => true, 'size' => 128),
            'zones' => array('type' => TYPE_STRING, 'required' => true, 'size' => 128),
            'amount_leader' => array('type' => TYPE_FLOAT, 'required' => true, 'size' => 128),
            'amount_follower' => array('type' => TYPE_FLOAT, 'required' => true, 'size' => 128),
            'amount_currency' => array('type' => TYPE_STRING, 'required' => true, 'size' => 128),
            'free_shipping' => array('type' => TYPE_BOOL, 'required' => true, 'size' => 1),
        ),
    );
}
