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

use Oyst\Classes\OneClickMerchantDiscount;
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
use Module;

class CartService extends AbstractOystService
{
    use ToolServiceTrait;

    /** @var AddressRepository */
    private $addressRepository;

    /** @var  ProductRepository */
    private $productRepository;

    /**
     * @param $data
     * @return string
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

        $amount_total  = 0;

        if ($data['context'] && isset($data['context']['id_user'])) {
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
                $firstname = Tools::substr($firstname, 0, Address::$definition['fields']['firstname']['size']);
            }

            $lastname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $data['user']['address']['last_name']);
            if (isset(Address::$definition['fields']['lastname']['size'])) {
                $lastname = Tools::substr($lastname, 0, Address::$definition['fields']['lastname']['size']);
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
            foreach ($data['items'] as $item) {
                $idProduct = $item['product']['reference'];
                $idCombination = 0;

                if (false  !== strpos($idProduct, ';')) {
                    $p = explode(';', $idProduct);
                    $idProduct = $p[0];
                    $idCombination = $p[1];
                }

                $product = new Product($idProduct);

                if (!$product->active || !$product->available_for_order) {
                    header('HTTP/1.1 400 Bad request');
                    header('Content-Type: application/json');
                    die(json_encode(array(
                        'code' => 'product-unavailable',
                        'message' => 'Unvailable product',
                    )));
                }

                $update_qty_result = $cart->updateQty($item['quantity'], (int)$idProduct, (int)$idCombination, false, 'up', $address->id);

                if (!$update_qty_result) {
                    header('HTTP/1.1 400 Bad request');
                    header('Content-Type: application/json');
                    die(json_encode(array(
                        'code' => 'stock-unavailable',
                        'message' => 'Unvailable stock',
                    )));
                }

                // Add items
                $price = $product->getPrice(
                    true,
                    $idCombination,
                    6,
                    null,
                    false,
                    true,
                    $item['quantity']
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

                    // Get attributes for title
                    if ($combination && $combination->id) {
                        $productRepository = new ProductRepository(Db::getInstance());
                        $attributesInfo = $productRepository->getAttributesCombination($combination);
                        foreach ($attributesInfo as $attributeInfo) {
                            $title .= ' '.$attributeInfo['value'];
                        }
                    }
                }

                $amount = new OystPrice($price, Context::getContext()->currency->iso_code);
                // Set amount total for cart rule with discount
                $amount_total += $price;

                $oneClickItem = new OneClickItem(
                    (string)$item['product']['reference'],
                    $amount,
                    (int)$item['quantity']
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

        // Manage cart rule
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        $cart_rules_in_cart = array();
        //Get potential cart rules which was auto added
        $auto_cart_rules = $this->context->cart->getCartRules();
        if (empty($data['context']['ids_cart_rule'])) {
            $data['context']['ids_cart_rule'] = array();
        }

        //Merge id auto_add with existent cart_rule
        foreach ($auto_cart_rules as $auto_cart_rule) {
            $data['context']['ids_cart_rule'][] = (int)$auto_cart_rule['id_cart_rule'];
            $cart_rules_in_cart[] = (int)$auto_cart_rule['id_cart_rule'];
        }

        $data['context']['ids_cart_rule'] = array_unique($data['context']['ids_cart_rule']);

        $free_shipping = false;

        if ($data['context']['ids_cart_rule'] != '') {
            foreach ($data['context']['ids_cart_rule'] as $id_cart_rule) {
                $cart_rule = new CartRule($id_cart_rule, $this->context->language->id);
                if (Validate::isLoadedObject($cart_rule)) {
                    if ($cart_rule->checkValidity($this->context, in_array($id_cart_rule, $cart_rules_in_cart), false)) {
                        if ($cart_rule->free_shipping) {
                            $free_shipping = true;
                        }
                    }
                }
            }
        }

        // Get carriers available
        $carriersAvailables = $cart->simulateCarriersOutput();
        // Get default shipment
        $id_default_carrier = (int)PSConfiguration::get('FC_OYST_SHIPMENT_DEFAULT');

        $type = OystCarrier::HOME_DELIVERY;

        $oyst_business_days = PSConfiguration::get('FC_OYST_BUSINESS_DAYS');
        $business_days = explode(',', $oyst_business_days);

        // Add carriers
        foreach ($carriersAvailables as $shipment) {
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
                if ($free_shipping) {
                    $amount = 0;
                } else {
                    $amount = (float) $shipment['price'];
                }

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

                if ($oyst_business_days) {
                    $delay_current = new \DateTime("NOW");
                    $delay_current->add(new \DateInterval("PT".$delay_shipment."H"));
                    $day_of_week = (int)$delay_current->format('N');
                    if (!in_array($day_of_week, $business_days)) {
                        do {
                            $delay_shipment += 24;
                            $delay_current->add(new \DateInterval("PT24H"));
                            $new_day_of_week = (int)$delay_current->format('N');
                        } while (!in_array($new_day_of_week, $business_days));
                    }
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

        if (empty($carriersAvailables)) {
            header('HTTP/1.1 400 Bad request');
            header('Content-Type: application/json');
            die(json_encode(array(
                'code' => 'no-shipment',
                'message' => 'Order has no shipment',
            )));
        } else {
            foreach ($carriersAvailables as $shipment) {
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
        }

        // Add first carrier if primary is not exist
        if (!$is_primary) {
            try {
                $oneClickOrderCartEstimate->setDefaultPrimaryShipmentByType();
            } catch (Exception $e) {
                header('HTTP/1.1 400 Bad request');
                header('Content-Type: application/json');
                die(json_encode(array(
                    'code' => 'no-primary-shipment',
                    'message' => $e->getMessage(),
                )));
            }
        }

        //For each cart_rule, check validity and if it's valid, add it to merchant_discount
        if ($data['context']['ids_cart_rule'] != '') {
            foreach ($data['context']['ids_cart_rule'] as $id_cart_rule) {
                $cart_rule = new CartRule($id_cart_rule, $this->context->language->id);
                if (Validate::isLoadedObject($cart_rule)) {
                    // die(var_dump($cart_rule->checkValidity($this->context, true, false)));
                    if ($cart_rule->checkValidity(
                        $this->context,
                        in_array($id_cart_rule, $cart_rules_in_cart),
                        false
                    )) {
                        $cart_rule_amount = 0;

                        if ((float)$cart_rule->reduction_percent != 0) {
                            $cart_rule_amount += $cart_rule->getContextualValue(true, $this->context);
                            $currency_iso_code = $this->context->currency->iso_code;
                        }

                        if ((float)$cart_rule->reduction_amount != 0) {
                            //Reduction amount case
                            $cart_rule_amount += $cart_rule->getContextualValue(true, $this->context);
                            $currency = new Currency($cart_rule->reduction_currency);
                            if (Validate::isLoadedObject($currency)) {
                                $currency_iso_code = $currency->iso_code;
                            } else {
                                $currency_iso_code = $this->context->currency->iso_code;
                            }
                        }
                        if ((int)$cart_rule->gift_product != 0) {
                            $reference = $cart_rule->gift_product;
                            $idProduct = (int)$cart_rule->gift_product;

                            if ($cart_rule->gift_product_attribute > 0) {
                                $reference .= ';'.$cart_rule->gift_product_attribute;
                                $idCombination = (int)$cart_rule->gift_product_attribute;
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
                                1
                            );

                            $images = array();
                            $images_pc = Image::getImages($this->context->language->id, $idProduct, $idCombination);
                            foreach ($images_pc as $image) {
                                $images[] = $this->context->link->getImageLink(
                                    $product->link_rewrite,
                                    $image['id_image']
                                );
                            }

                            //If no image for attribute, search default product image
                            if (empty($images)) {
                                foreach (Image::getImages($this->context->language->id, $idProduct) as $image) {
                                    $images[] = $this->context->link->getImageLink(
                                        $product->link_rewrite,
                                        $image['id_image']
                                    );
                                }
                            }

                            $oneClickItemFree->__set('title', $title);
                            $oneClickItemFree->__set('message', $cart_rule->description);
                            $oneClickItemFree->__set('images', $images);
                            $oneClickOrderCartEstimate->addFreeItems($oneClickItemFree);
                        }

                        if ($cart_rule_amount > 0) {
                            if ($cart_rule_amount > $amount_total) {
                                $cart_rule_amount = $amount_total;
                            }
                            $oyst_price = new OystPrice($cart_rule_amount, $currency_iso_code);
                            $merchand_discount = new OneClickMerchantDiscount($oyst_price, $cart_rule->name);
                            $oneClickOrderCartEstimate->addMerchantDiscount($merchand_discount);
                        }
                    }
                }
            }
        }

        if (Module::isInstalled('giftonordermodule') && Module::isEnabled('giftonordermodule')) {
            if ($data['context']['id_cart'] && (int)$data['context']['id_cart'] > 0) {
                $sql = 'SELECT go.*
                        FROM `'._DB_PREFIX_.'giftonorder_order` as go
                        WHERE go.id_cart = '.(int)$data['context']['id_cart'];

                $giftInCart = Db::getInstance()->ExecuteS($sql);
                if (!$giftInCart) {
                    $giftInCart = array();
                }
                if (count($giftInCart) > 0) {
                    foreach ($giftInCart as $gift) {
                        $idCombination = $gift['id_combination'];
                        $reference = $gift['id_product'].';'.$idCombination;
                        $product = new Product($gift['id_product'], false, $this->context->language->id);
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
                            1
                        );

                        $images = array();
                        foreach (Image::getImages($this->context->language->id, $idProduct, $idCombination) as $image) {
                            $images[] = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
                        }

                        //If no image for attribute, search default product image
                        if (empty($images)) {
                            foreach (Image::getImages($this->context->language->id, $idProduct) as $image) {
                                $images[] = $this->context->link->getImageLink(
                                    $product->link_rewrite,
                                    $image['id_image']
                                );
                            }
                        }

                        $giftonorder = new \Giftonorder($gift['id_giftonorder']);

                        $oneClickItemFree->__set('title', $title);
                        $oneClickItemFree->__set('message', $giftonorder->name);
                        $oneClickItemFree->__set('images', $images);
                        $oneClickOrderCartEstimate->addFreeItems($oneClickItemFree);
                    }
                }
            }
        }

        $cart_amount = $cart->getOrderTotal(true);

        if ($cart_amount > 0) {
            $cart_amount_oyst = new OystPrice($cart->getOrderTotal(true), Context::getContext()->currency->iso_code);
            $oneClickOrderCartEstimate->setCartAmount($cart_amount_oyst);
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
