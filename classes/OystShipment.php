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

class OystShipment extends \ObjectModel
{
    /** @var  int */
    public $id_carrier_reference;

    /** @var  bool */
    public $primary;

    /** @var  string */
    public $type;

    /** @var  int */
    public $delay;

    /** @var  string Json */
    public $zones;

    /** @var  float */
    public $amount_leader;

    /** @var  float */
    public $amount_follower;

    /** @var  float */
    public $free_shipping;

    /** @var  string */
    public $currency;

    public static $definition = array(
        'table' => 'oyst_shipment',
        'primary' => 'id_oyst_shipment',
        'fields' => array(
            'id_carrier_reference' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'primary' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'type' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'delay' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'zones' => array('type' => self::TYPE_STRING),
            'amount_leader' => array('type' => self::TYPE_FLOAT),
            'amount_follower' => array('type' => self::TYPE_FLOAT),
            'free_shipping' => array('type' => self::TYPE_FLOAT),
            'currency' => array('type' => self::TYPE_STRING),
        )
    );

    /**
     * @return int
     */
    public function getIdCarrierReference()
    {
        return $this->id_carrier_reference;
    }

    /**
     * @param int $id_carrier_reference
     * @return OystShipment
     */
    public function setIdCarrierReference($id_carrier_reference)
    {
        $this->id_carrier_reference = $id_carrier_reference;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return $this->primary;
    }

    /**
     * @param bool $primary
     * @return OystShipment
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return OystShipment
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     * @return OystShipment
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;

        return $this;
    }

    /**
     * @return string
     */
    public function getZones()
    {
        return $this->zones;
    }

    /**
     * @param string $zones
     * @return OystShipment
     */
    public function setZones($zones)
    {
        $this->zones = $zones;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmountLeader()
    {
        return $this->amount_leader;
    }

    /**
     * @param float $amount_leader
     * @return OystShipment
     */
    public function setAmountLeader($amount_leader)
    {
        $this->amount_leader = $amount_leader;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmountFollower()
    {
        return $this->amount_follower;
    }

    /**
     * @param float $amount_follower
     * @return OystShipment
     */
    public function setAmountFollower($amount_follower)
    {
        $this->amount_follower = $amount_follower;

        return $this;
    }

    /**
     * @return float
     */
    public function getFreeShipping()
    {
        return $this->free_shipping;
    }

    /**
     * @param float $free_shipping
     * @return OystShipment
     */
    public function setFreeShipping($free_shipping)
    {
        $this->free_shipping = $free_shipping;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return OystShipment
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }
}
