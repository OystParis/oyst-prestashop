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

namespace Oyst\Service;

use Oyst\Classes\OneClickOrderCartEstimate;
use Oyst\Classes\OneClickShipmentCatalogLess;
use Oyst\Classes\OneClickItem;
use Oyst\Classes\OystCarrier;
use Oyst\Classes\OystPrice;
use Oyst\Repository\AddressRepository;
use Oyst\Repository\ProductRepository;
use Db;
use Customer;
use Cart;
use Address;
use Tools;
use Validate;
use Currency;
use Context;
use Product;
use Tax;
use TaxCalculator;
use Carrier;
use Country;
use Configuration as PSConfiguration;
use Exception;
use StockAvailable;
use Combination;
use CartRule;
use Image;

class CartService extends AbstractOystService
{
    /** @var AddressRepository */
    private $addressRepository;

    /** @var  ProductRepository */
    private $productRepository;

    /**
     * @param $user
     * @return Customer
     */
    private function getCustomer($user)
    {
        $customerInfo = Customer::getCustomersByEmail($user['email']);
        if (count($customerInfo)) {
            $customer = new Customer($customerInfo[0]['id_customer']);
        } else {
            $firstname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $user['first_name']);
            if (isset(Customer::$definition['fields']['firstname']['size'])) {
                $firstname = substr($firstname, 0, Customer::$definition['fields']['firstname']['size']);
            }

            $lastname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $user['last_name']);
            if (isset(Customer::$definition['fields']['lastname']['size'])) {
                $lastname = substr($lastname, 0, Customer::$definition['fields']['lastname']['size']);
            }

            $customer = new Customer();
            $customer->email = $user['email'];
            $customer->firstname = $firstname;
            $customer->lastname = $lastname;
            if (version_compare(_PS_VERSION_, '1.5.4.0', '>=')) {
                $customer->id_lang = PSConfiguration::get('PS_LANG_DEFAULT');
            }
            $customer->passwd = Tools::encrypt(Tools::passwdGen());
            $customer->add();
        }

        return $customer;
    }

    /**
     * @param $data
     * @return array
     * @throws Exeption
     */
    public function estimate($data)
    {

        // Set delay carrier in hours
        $delay = array(
            0 => 72,
            1 => 216,
            2 => 192,
            3 => 168,
            4 => 144,
            5 => 120,
            6 => 96,
            7 => 72,
            8 => 48,
            9 => 24
        );

        if ($data['context'] && $data['context']['id_user']) {
            $customer = new Customer((int)$data['context']['id_user']);
        } else {
            $customer = $this->getCustomer($data['user']);
        }
        if (!Validate::isLoadedObject($customer)) {
            $this->logger->emergency(
                'Customer not found or can\'t be found ['.json_encode($customer).']'
            );
        }

        $addressRepository = new AddressRepository(Db::getInstance());

        $address = $addressRepository->findAddress($data['user']['address'], $customer);

        if (!Validate::isLoadedObject($address)) {
            $countryId = (int)Country::getByIso($data['user']['address']['country']);
            if (0 >= $countryId) {
                $countryId = PSConfiguration::get('PS_COUNTRY_DEFAULT');
            }

            $firstname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $data['user']['address']['first_name']);
            if (isset(Address::$definition['fields']['firstname']['size'])) {
                $firstname = substr($firstname, 0, Address::$definition['fields']['firstname']['size']);
            }

            $lastname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $data['user']['address']['last_name']);
            if (isset(Address::$definition['fields']['lastname']['size'])) {
                $lastname = substr($lastname, 0, Address::$definition['fields']['lastname']['size']);
            }

            $address = new Address();
            $address->id_customer = $customer->id;
            $address->firstname = $firstname;
            $address->lastname = $lastname;
            $address->address1 = $data['user']['address']['street'];
            $address->postcode = $data['user']['address']['postcode'];
            $address->city = $data['user']['address']['city'];
            $address->alias = 'OystAddress';
            $address->id_country = $countryId;
            $address->phone = $data['user']['phone'];
            $address->phone_mobile = $data['user']['phone'];

            $address->add();
        } else {
            //Fix for retroactivity for missing phone bug or phone
            if ($address->phone_mobile == '' || $address->phone == '') {
                $address->phone = $data['user']['phone'];
                $address->phone_mobile = $data['user']['phone'];
                $address->update();
            }
        }

        $this->logger->info(
            sprintf(
                'New notification address [%s]',
                json_encode($address)
            )
        );

        // PS core used this context anywhere.. So we need to fill it properly
        $this->context->cart = $cart = new Cart();
        $this->context->customer = $customer;
        // For debug but when prod pass in context object currency
        $this->context->currency = new Currency(Currency::getIdByIsoCode('EUR'));

        $cart->id_customer = $customer->id;
        $cart->id_address_delivery = $address->id;
        $cart->id_address_invoice = $address->id;
        $cart->id_lang = $this->context->language->id;
        $cart->secure_key = $customer->secure_key;
        $cart->id_shop = PSConfiguration::get('PS_SHOP_DEFAULT');
        $cart->id_currency = $this->context->currency->id;

        if (!$cart->add()) {
            $this->logger->emergency(
                'Can\'t create cart ['.json_encode($cart).']'
            );
            return false;
        }

        $oneClickOrderCartEstimate = new OneClickOrderCartEstimate(array());

        if (isset($data['items'])) {
            foreach ($data['items'] as $key => $item) {
                $idProduct = $item['product']['reference'];
                $idCombination = 0;

                if (false  !== strpos($idProduct, ';')) {
                    $p = explode(';', $idProduct);
                    $idProduct = $p[0];
                    $idCombination = $p[1];
                }

                $product = new Product($idProduct);

                if (PSConfiguration::get('FC_OYST_SHOULD_AS_STOCK') && _PS_VERSION_ >= '1.6.0.0') {
                    if ($product->advanced_stock_management == 0) {
                        StockAvailable::updateQuantity($idProduct, $idCombination, $item['product']['quantity']);
                    }
                }

                $cart->updateQty($item['product']['quantity'], (int)$idProduct, (int)$idCombination, false, 'up', $address->id);

                if (PSConfiguration::get('FC_OYST_SHOULD_AS_STOCK') && _PS_VERSION_ >= '1.6.0.0') {
                    if ($product->advanced_stock_management == 0) {
                        StockAvailable::updateQuantity($idProduct, $idCombination, -$item['product']['quantity']);
                    }
                }

                // Add items

                $price = $product->getPrice(
                    true,
                    $idCombination,
                    6,
                    null,
                    false,
                    true,
                    $item['product']['quantity']
                );

                $without_reduc_price = $product->getPriceWithoutReduct(
                    false,
                    $idCombination
                );

                $title = is_array($product->name) ? reset($product->name) : $product->name;

                if ($idCombination > 0) {
                    $combination = new Combination($idCombination);
                    if (!Validate::isLoadedObject($combination)) {
                        $this->logger->emergency(
                            'Combination not exist ['.json_encode($data).']'
                        );
                    }
                }

                // Get attributes for title
                if ($combination && $combination->id) {
                    $productRepository = new ProductRepository(Db::getInstance());
                    $attributesInfo = $productRepository->getAttributesCombination($combination);
                    foreach ($attributesInfo as $attributeInfo) {
                        $title .= ' '.$attributeInfo['value'];
                    }
                }

                $amount = new OystPrice($price, Context::getContext()->currency->iso_code);
                $oneClickItem = new OneClickItem(
                    (string)$item['product']['reference'],
                    $amount,
                    (int)$item['product']['quantity']
                );

                $crossed_out_amount = new OystPrice($without_reduc_price, Context::getContext()->currency->iso_code);
                if ($amount != $crossed_out_amount) {
                    $oneClickItem->__set('crossedOutAmount', $crossed_out_amount);
                }
                $oneClickOrderCartEstimate->addItem($oneClickItem);
            }
        } else {
            $this->logger->emergency(
                'Items not exist ['.json_encode($data).']'
            );
            return false;
        }


        // Get carriers available
        $carriersAvailables = $cart->simulateCarriersOutput();
        // Get default shipment
        $id_default_carrier = (int)PSConfiguration::get('FC_OYST_SHIPMENT_DEFAULT');

        $type = OystCarrier::HOME_DELIVERY;

        // Add carriers
        foreach ($carriersAvailables as $key => $shipment) {
            $id_carrier = (int)Tools::substr(Cart::desintifier($shipment['id_carrier']), 0, -1); // Get id carrier

            $id_reference = Db::getInstance()->getValue(
                'SELECT `id_reference`
                FROM `'._DB_PREFIX_.'carrier`
                WHERE id_carrier = '.(int)$id_carrier
            );

            $type_shipment = PSConfiguration::get("FC_OYST_SHIPMENT_".$id_reference);
            $delay_shipment = PSConfiguration::get("FC_OYST_SHIPMENT_DELAY_".$id_reference);

            if (isset($type_shipment) &&
                $type_shipment != '0'
            ) {
                $type = $type_shipment;

                // Get amount with tax
                $carrier = new Carrier($id_carrier);
                $amount = (float) $shipment['price'];

                $oystPrice = new OystPrice($amount, Context::getContext()->currency->iso_code);
                $oystCarrier = new OystCarrier($id_carrier, $shipment['name'], $type);

                $primary = false;
                if ($carrier->id_reference == $id_default_carrier) {
                    $primary =  true;
                }

                if ($delay_shipment && $delay_shipment != '') {
                    $delay_shipment = (int)$delay_shipment * 24;
                } else {
                    $delay_shipment = $delay[(int)$carrier->grade];
                }

                $oneClickShipment = new OneClickShipmentCatalogLess(
                    $oystPrice,
                    $delay_shipment,
                    $oystCarrier,
                    $primary
                );

                $oneClickOrderCartEstimate->addShipment($oneClickShipment);
            }
        }

        // Check exist primary
        $is_primary = false;

        foreach ($carriersAvailables as $key => $shipment) {
            $carrier_desintifier = Cart::desintifier($shipment['id_carrier']);
            $id_carrier = (int)Tools::substr($carrier_desintifier, 0, -1);
            $id_reference = Db::getInstance()->getValue('
                SELECT `id_reference`
                FROM `'._DB_PREFIX_.'carrier`
                WHERE id_carrier = '.(int)$id_carrier);
            if ($id_reference == $id_default_carrier) {
                $is_primary = true;
            }
        }

        // Add first carrier if primary is not exist
        if (!$is_primary) {
            $oneClickOrderCartEstimate->setDefaultPrimaryShipmentByType();
        }

        // Manage cart rule
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        // Get cart rule group customer
        //$cart_rules = CartRule::getCustomerCartRules(Context::getContext()->cookie->id_lang, Context::getContext()->cookie->id_customer, true, true, false, $this, true);
        // Get cart rule with product gift
        $cart_rules_gift = $this->context->cart->getCartRules(CartRule::FILTER_ACTION_GIFT);

        foreach ($cart_rules_gift as $product_gift) {
            $reference = $product_gift['gift_product'];
            $idProduct = (int)$product_gift['gift_product'];

            if ($product_gift['gift_product_attribute'] > 0) {
                $reference .= ';'.$product_gift['gift_product_attribute'];
                $idCombination = (int)$product_gift['gift_product_attribute'];
            }

            $product = new Product($idProduct, false, $this->context->language->id);

            $title = is_array($product->name) ? reset($product->name) : $product->name;

            if ($idCombination > 0) {
                $combination = new Combination($idCombination);
                if (!Validate::isLoadedObject($combination)) {
                    $this->logger->emergency(
                        'Combination not exist ['.json_encode($data).']'
                    );
                }
            }

            // Get attributes for title
            if ($combination && $combination->id) {
                $productRepository = new ProductRepository(Db::getInstance());
                $attributesInfo = $productRepository->getAttributesCombination($combination);
                foreach ($attributesInfo as $attributeInfo) {
                    $title .= ' '.$attributeInfo['value'];
                }
            }

            $amount = new OystPrice(0, Context::getContext()->currency->iso_code);
            $oneClickItemFree = new OneClickItem(
                (string)$reference,
                $amount,
                (int)$product_gift['quantity']
            );

            $images = array();
            foreach (Image::getImages($this->context->language->id, $idProduct, $idCombination) as $image) {
                $images[] = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
            }

            //If no image for attribute, search default product image
            if (empty($images)) {
                foreach (Image::getImages($this->context->language->id, $idProduct) as $image) {
                    $images[] = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
                }
            }

            $oneClickItemFree->__set('title', $title);
            $oneClickItemFree->__set('message', $product_gift['description']);
            $oneClickItemFree->__set('images', $images);
            $oneClickOrderCartEstimate->addFreeItems($oneClickItemFree);
        }

        $this->logger->info(
            sprintf(
                'New notification oneClickOrderCartEstimate [%s]',
                $oneClickOrderCartEstimate->toJson()
            )
        );

        // Delete cart for module relaunch cart
        $cart->delete();

        return $oneClickOrderCartEstimate->toJson();
    }
}
