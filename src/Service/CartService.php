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

use Db;
use Cache;
use Tax;
use Cart;
use Group;
use Image;
use Tools;
use Module;
use Address;
use Carrier;
use Context;
use Country;
use Product;
use CartRule;
use Currency;
use Customer;
use Validate;
use Exception;
use Combination;
use TaxCalculator;
use StockAvailable;
use Oyst\Classes\OystPrice;
use Oyst\Classes\OystCarrier;
use Oyst\Classes\OneClickItem;
use Configuration as PSConfiguration;
use Oyst\Repository\AddressRepository;
use Oyst\Repository\ProductRepository;
use Oyst\Classes\OneClickMerchantDiscount;
use Oyst\Classes\OneClickOrderCartEstimate;
use Oyst\Classes\OneClickShipmentCatalogLess;

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
        $this->context->cookie->iso_code_country = 'FR';

        if (isset($data['discount_coupon'])) {
            $discount_coupon = $data['discount_coupon'];
        }
        $data = $data['order'];
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

        // PS core used this context anywhere.. So we need to fill it properly
        if ($data['context'] && isset($data['context']['id_cart'])) {
            $this->context->cart = $cart = new Cart((int)$data['context']['id_cart']);
            $this->context->cookie->id_cart = $data['context']['id_cart'];
        } else {
            $this->context->cart = $cart = new Cart();
        }

        //Check if no items => remove cart
        if (empty($data['items'])) {
            if (Validate::isLoadedObject($cart)) {
                $cart->delete();
            }

            return json_encode(array());
        }

        $oldIdAddressDelivery = (int)$cart->id_address_delivery;

        // $this->context->customer = $customer;
        // For debug but when prod pass in context object currency
        $this->context->currency = new Currency(Currency::getIdByIsoCode('EUR'));

        $customer = null;

        if ($id_customer = Customer::customerExists($data['user']['email'], true)) {
            $customer = new Customer($id_customer);
        }

        $usetax = true;

        if ($customer) {
            if (Group::getPriceDisplayMethod($customer->id_default_group) == 1) {
                $usetax = false;
            }
        }

        $countryId = (int)Country::getByIso($data['user']['address']['country']);
        if (0 >= $countryId) {
            $countryId = PSConfiguration::get('PS_COUNTRY_DEFAULT');
        }

        $id_zone = Country::getIdZone($countryId);

        if ($customer) {
            if (!Validate::isLoadedObject($customer)) {
                $this->logger->emergency(
                    'Customer not found or can\'t be found ['.json_encode($customer).']'
                );
            }

            $addressRepository = new AddressRepository(Db::getInstance());

            $address = $addressRepository->findAddress($data['user']['address'], $customer);

            if (!Validate::isLoadedObject($address)) {
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
                $address->phone = $data['user']['phone']? $data['user']['phone'] : '';
                $address->phone_mobile = $data['user']['phone']? $data['user']['phone'] : '';

                if (isset($data['user']['company_name'])) {
                    $address->company = trim(preg_replace('/^[0-9!<>,;?=+()@#\"°{}_$%:]*/u', '', $data['user']['company_name']));
                }

                if (isset($data['user']['complementary'])) {
                    $address->address2 = trim(preg_replace('/^[0-9!<>,;?=+()@#\"°{}_$%:]*/u', '', $data['user']['complementary']));
                }

                if (!$address->add()) {
                    $this->logger->emergency(
                        'Can\'t create address ['.json_encode($address).']'
                    );
                    return false;
                }
            } else {
                //Fix for retroactivity for missing phone bug or phone
                if ($address->phone_mobile == '' || $address->phone == '') {
                    $address->phone = $data['user']['phone'];
                    $address->phone_mobile = $data['user']['phone'];
                }

                if (isset($data['user']['address']['company_name'])) {
                    $address->company = trim(preg_replace('/^[0-9!<>,;?=+()@#\"°{}_$%:]*/u', '', $data['user']['address']['company_name']));
                }

                if (isset($data['user']['address']['complementary'])) {
                    $address->address2 = trim(preg_replace('/^[0-9!<>,;?=+()@#\"°{}_$%:]*/u', '', $data['user']['address']['complementary']));
                }

                if (!$address->update()) {
                    $this->logger->emergency(
                        'Can\'t update address ['.json_encode($address).']'
                    );
                    return false;
                }
            }

            $this->logger->info(
                sprintf(
                    'New notification address [%s]',
                    json_encode($address)
                )
            );

            $cart->id_customer = $customer->id;
            $cart->id_address_delivery = $address->id;
            $cart->id_address_invoice = $address->id;
            $cart->secure_key = $customer->secure_key;
        } else {
            $cart->id_customer = 0;
            $cart->secure_key = 0;


            if ($data['context'] && isset($data['context']['id_address'])) {
                $firstname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $data['user']['address']['first_name']);
                if (isset(Address::$definition['fields']['firstname']['size'])) {
                    $firstname = Tools::substr($firstname, 0, Address::$definition['fields']['firstname']['size']);
                }

                $lastname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $data['user']['address']['last_name']);
                if (isset(Address::$definition['fields']['lastname']['size'])) {
                    $lastname = Tools::substr($lastname, 0, Address::$definition['fields']['lastname']['size']);
                }

                $address_fake = new Address($data['context']['id_address']);
                $address_fake->firstname = $firstname;
                $address_fake->lastname = $lastname;
                $address_fake->address1 = $data['user']['address']['street'];
                $address_fake->postcode = $data['user']['address']['postcode'];
                $address_fake->city = $data['user']['address']['city'];
                $address_fake->alias = 'OystAddress';
                $address_fake->id_country = $countryId;
                $address_fake->phone = $data['user']['phone']? $data['user']['phone'] : '';
                $address_fake->phone_mobile = $data['user']['phone']? $data['user']['phone'] : '';

                $address_fake->update();

                $cart->id_address_delivery = $address_fake->id;
                $cart->id_address_invoice = $address_fake->id;
            } else {
                $cart->id_address_delivery = 0;
                $cart->id_address_invoice = 0;
            }
        }
        $cart->id_lang = $this->context->language->id;
        $cart->id_shop = PSConfiguration::get('PS_SHOP_DEFAULT');
        $cart->id_currency = $this->context->currency->id;


        if (!$cart->save()) {
            $this->logger->emergency(
                'Can\'t save cart ['.json_encode($cart).']'
            );
            return false;
        }

        if ($oldIdAddressDelivery) {
            $cart->updateAddressId($oldIdAddressDelivery, $cart->id_address_delivery);
        }

        $oneClickOrderCartEstimate = new OneClickOrderCartEstimate(array());

        $oystProducts = array();

        if (isset($data['items'])) {
            foreach ($data['items'] as $key => $itemOyst) {
                $oystProducts[$itemOyst['product']['reference']]= $itemOyst['quantity'];
            }
        }

        $products_gift = $cart->getSummaryDetails()['gift_products'];

        if ($cart->id && count($cart->getProducts()) > 0 && !$cart->isVirtualCart()) {
            foreach ($cart->getProducts() as $item) {
                $idProduct = $item['id_product'];
                $idCombination = $item['id_product_attribute'];

                if ($idCombination > 0) {
                    $reference = (string)$idProduct.';'.$idCombination;
                } else {
                    $reference = (string)$idProduct;
                }

                if (count($oystProducts) > 0 && in_array($reference, array_keys($oystProducts))) {
                    $delta_quantity = $oystProducts[$reference] - $item['cart_quantity'];

                    if ($delta_quantity != 0) {
                        if ($delta_quantity < 0) {
                            $direction = 'down';
                        } else {
                            $direction = 'up';
                        }

                        $update_qty_result = $cart->updateQty(
                            abs($delta_quantity),
                            (int)$idProduct,
                            (int)$idCombination,
                            false,
                            $direction,
                            $cart->id_address_delivery
                        );

                        if (!$update_qty_result) {
                            return json_encode(array(
                                'error' => true,
                                'code' => 'stock-unavailable',
                                'message' => 'Unvailable stock',
                            ));
                        }
                    }

                    $product = new Product($idProduct);

                    if (!$product->active || !$product->available_for_order) {
                        return json_encode(array(
                            'error' => true,
                            'code' => 'product-unavailable',
                            'message' => 'Unvailable product',
                        ));
                    }

                    // Add items
                    $price = Product::getPriceStatic(
                        $product->id,
                        $usetax,
                        $idCombination,
                        6,
                        null,
                        false,
                        true,
                        $oystProducts[$reference],
                        false,
                        $cart->id_customer,
                        $cart->id,
                        $cart->id_address_invoice
                    );

                    $without_reduc_price = $product->getPriceWithoutReduct(
                        !$usetax,
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

                    $oneClickItem = new OneClickItem(
                        $reference,
                        $amount,
                        (int)$oystProducts[$reference]
                    );

                    $crossed_out_amount = new OystPrice($without_reduc_price, Context::getContext()->currency->iso_code);
                    if ($amount != $crossed_out_amount) {
                        $oneClickItem->__set('crossedOutAmount', $crossed_out_amount);
                    }
                    $oneClickOrderCartEstimate->addItem($oneClickItem);
                } else {
                    if ($products_gift && count($oystProducts) > 0) {
                        foreach ($products_gift as $gift) {
                            if ($gift['id_product'] != $idProduct && $gift['id_product_attribute'] != $idCombination) {
                                $cart->deleteProduct($idProduct, $idCombination);
                            }
                        }
                    } else {
                        $cart->deleteProduct($idProduct, $idCombination);
                    }
                }
            }
        } elseif ($cart->isVirtualCart()) {
            $shipments = array();
            return json_encode($shipments);
        } elseif (count($oystProducts) == 0) {
            foreach ($cart->getProducts() as $item) {
                $idProduct = $item['id_product'];
                $idCombination = $item['id_product_attribute'];

                $cart->deleteProduct($idProduct, $idCombination);
            }
        } else {
            $this->logger->emergency(
                'Items not exist ['.json_encode($data).']'
            );
            return json_encode(array(
                'error' => true,
                'code' => 'stock-unavailable',
                'message' => 'Unvailable stock',
            ));
        }

        // Manage cart rule
        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        $cart_rules_in_cart = array();

        if (isset($discount_coupon)) {
            if (CartRule::cartRuleExists($discount_coupon)) {
                $this->context->cart->addCartRule((int)CartRule::getIdByCode($discount_coupon));
            } else {
                $oneClickOrderCartEstimate->setDiscountCouponError(Tools::displayError('The voucher code is invalid.'));
            }
        }

        //Get potential cart rules which was auto added
        $test = $this->context->cart->getCartRules();
        if (empty($data['context']['ids_cart_rule'])) {
            $data['context']['ids_cart_rule'] = array();
        }

        //Merge id auto_add with existent cart_rule
        foreach ($test as $auto_cart_rule) {
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

        // Check général free shipping condition
        // Free fees
        $free_fees_price = 0;
        if (PSConfiguration::hasKey('PS_SHIPPING_FREE_PRICE')) {
            $free_fees_price = Tools::convertPrice((float)PSConfiguration::get('PS_SHIPPING_FREE_PRICE'), Currency::getCurrencyInstance((int)$cart->id_currency));
        }
        $orderTotalwithDiscounts = $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, null, null, false);
        if ($orderTotalwithDiscounts >= (float)($free_fees_price) && (float)($free_fees_price) > 0) {
            $free_shipping = true;
        }

        if (PSConfiguration::hasKey('PS_SHIPPING_FREE_WEIGHT')
            && $cart->getTotalWeight() >= (float)PSConfiguration::get('PS_SHIPPING_FREE_WEIGHT')
            && (float)PSConfiguration::get('PS_SHIPPING_FREE_WEIGHT') > 0) {
            $free_shipping = true;
        }

        // Clean shipping cost cache
        Cache::clean('getPackageShippingCost_*');

        // Get carriers available
        $carriersAvailables = $cart->simulateCarriersOutput(null, true);

        // Get default shipment
        $id_default_carrier = (int)PSConfiguration::get('FC_OYST_SHIPMENT_DEFAULT');

        $type = OystCarrier::HOME_DELIVERY;

        $id_zone = Address::getZoneById((int)$cart->id_address_delivery);

        $oyst_business_days = PSConfiguration::get('FC_OYST_BUSINESS_DAYS');
        $business_days = explode(',', $oyst_business_days);

        // Add carriers
        foreach ($carriersAvailables as $shipment) {
            $id_carrier = (int)Tools::substr(Cart::desintifier($shipment['id_carrier']), 0, -1); // Get id carrier
            $id_reference = $this->getReferenceCarrier($id_carrier);

            $type_shipment = PSConfiguration::get("FC_OYST_SHIPMENT_".$id_reference);
            $delay_shipment = PSConfiguration::get("FC_OYST_SHIPMENT_DELAY_".$id_reference);

            if (!empty($type_shipment)) {
                $type = $type_shipment;

                // Get amount with tax
                $carrier = new Carrier($id_carrier);
                if ($free_shipping) {
                    $amount = 0;
                } else {
                    $shipping_method = $carrier->getShippingMethod();

                    if ($shipping_method == Carrier::SHIPPING_METHOD_WEIGHT) {
                        $shipping = $carrier->getDeliveryPriceByWeight($cart->getTotalWeight($cart->getProducts(false)), (int)$id_zone);

                        $carrier_tax = Tax::getCarrierTaxRate($id_carrier, $cart->id_address_delivery);

                        if (isset($carrier_tax)) {
                            $shipping *= 1 + ($carrier_tax / 100);
                        }
                        $amount = (float)$shipping;
                    } else {
                        $amount = (float)$shipment['price'];
                    }
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
            return json_encode(array(
                'error' => true,
                'code' => 'no-shipment',
                'message' => 'Order has no shipment',
            ));
        } else {
            foreach ($carriersAvailables as $shipment) {
                $carrier_desintifier = Cart::desintifier($shipment['id_carrier']);
                $id_carrier = (int)Tools::substr($carrier_desintifier, 0, -1);
                $id_reference = $this->getReferenceCarrier($id_carrier);
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
                return json_encode(array(
                    'error' => true,
                    'code' => 'no-primary-shipment',
                    'message' => $e->getMessage(),
                ));
            }
        }

        $cart_products_amount = $cart->getOrderTotal($usetax, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING, $cart->getProducts());

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
                            if ($cart_rule_amount > $cart_products_amount) {
                                $cart_rule_amount = $cart_products_amount;
                            }

                            $oyst_price = new OystPrice($cart_rule_amount, $currency_iso_code);
                            $merchand_discount = new OneClickMerchantDiscount($oyst_price, $cart_rule->name);
                            $oneClickOrderCartEstimate->addMerchantDiscount($merchand_discount);
                        }
                    } else {
                        $oneClickOrderCartEstimate->setDiscountCouponError(Tools::displayError('The voucher code is invalid.'));
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

                        $sql_name_gift = 'SELECT name
                            FROM ps_giftonorder_lang
                            WHERE  id_giftonorder ='.(int)$gift['id_giftonorder'].
                            ' AND  id_lang = '.(int)$this->context->language->id;
                        $name_gift = Db::getInstance()->getValue($sql_name_gift);

                        $oneClickItemFree->__set('title', $title);
                        $oneClickItemFree->__set('message', $name_gift);
                        $oneClickItemFree->__set('images', $images);
                        $oneClickOrderCartEstimate->addFreeItems($oneClickItemFree);
                    }
                }
            }
        }

        if (Module::isInstalled('bestkit_gifts') && Module::isEnabled('bestkit_gifts')) {
            if ($data['context']['id_cart'] && (int)$data['context']['id_cart'] > 0) {
                $sql = 'SELECT go.*
                        FROM `'._DB_PREFIX_.'bestkit_gift_cart` as go
                        WHERE go.id_cart = '.(int)$data['context']['id_cart'];

                $giftInCart = Db::getInstance()->ExecuteS($sql);
                if (!$giftInCart) {
                    $giftInCart = array();
                }
                if (count($giftInCart) > 0) {
                    foreach ($giftInCart as $gift) {
                        $idCombination = $gift['id_product_attribute'];
                        $reference = $gift['id_product'].';'.$idCombination;
                        $product = new Product($gift['id_product'], false, $this->context->language->id);
                        $title_bestkit_gift = is_array($product->name) ? reset($product->name) : $product->name;

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
                                $title_bestkit_gift .= ' '.$attributeInfo['value'];
                            }
                        }

                        $amount = new OystPrice(0, Context::getContext()->currency->iso_code);
                        $oneClickItemFree = new OneClickItem(
                            (string)$reference,
                            $amount,
                            1
                        );

                        $images = array();
                        foreach (Image::getImages($this->context->language->id, $gift['id_product'], $idCombination) as $image) {
                            $images[] = $this->context->link->getImageLink($product->link_rewrite, $image['id_image']);
                        }

                        //If no image for attribute, search default product image
                        if (empty($images)) {
                            foreach (Image::getImages($this->context->language->id, $gift['id_product']) as $image) {
                                $images[] = $this->context->link->getImageLink(
                                    $product->link_rewrite,
                                    $image['id_image']
                                );
                            }
                        }

                        $sql_name_gift = 'SELECT name
                            FROM `'._DB_PREFIX_.'bestkit_gift_rule_lang`
                            WHERE  id_bestkit_gift_rule ='.(int)$gift['id_bestkit_gift_rule'].
                            ' AND  id_lang = '.(int)$this->context->language->id;
                        $name_gift = Db::getInstance()->getValue($sql_name_gift);

                        $oneClickItemFree->__set('title', $name_gift);
                        $oneClickItemFree->__set('message', $name_gift);
                        $oneClickItemFree->__set('images', $images);
                        $oneClickOrderCartEstimate->addFreeItems($oneClickItemFree);
                    }
                }
            }
        }

        // Get carrier selected
        if ($data['shipment'] != null) {
            $id_carrier_selected = (int)$data['shipment']['id'];
        } else {
            $carrier = Carrier::getCarrierByReference($id_default_carrier);
            if (Validate::isLoadedObject($carrier)) {
                $id_carrier_selected = $carrier->id;
            }
        }

        $carrierZone = true;
        // Get carrier available for zone
        if ($id_carrier_selected) {
            $carrierZone = Carrier::checkCarrierZone($id_carrier_selected, $id_zone);
        }

        // If carrier is null or carrier is not available for zone
        if ($id_carrier_selected === null || !$carrierZone) {
            foreach ($carriersAvailables as $shipment) {
                // Get id carrier
                $id_carrier = (int)Tools::substr(Cart::desintifier($shipment['id_carrier']), 0, -1);
                $id_reference = $this->getReferenceCarrier($id_carrier);

                // Get type of carrier
                $type_shipment = PSConfiguration::get("FC_OYST_SHIPMENT_".$id_reference);
                if ($type_shipment === OystCarrier::HOME_DELIVERY && Carrier::checkCarrierZone($id_carrier, $id_zone)) {
                    $id_carrier_selected = $id_carrier;
                    break;
                }
            }
        }

        $delivery_option[$cart->id_address_delivery] = $id_carrier_selected.',';
        $cart->setDeliveryOption($delivery_option);
        $cart->id_carrier = $id_carrier_selected;
        $cart->save();

        $with_tax = Tax::getCarrierTaxRate($id_carrier_selected, $cart->id_address_delivery);

        $cart_shipping_amount = $cart->getOrderTotal($with_tax, Cart::ONLY_SHIPPING, null, $id_carrier_selected);
        $cart_discount_amount = $cart->getOrderTotal($usetax, Cart::ONLY_DISCOUNTS);

        $cart_amount = ($cart_products_amount + $cart_shipping_amount) - $cart_discount_amount;

        if ($cart_amount > 0) {
            $cart_amount_oyst = new OystPrice($cart_amount, Context::getContext()->currency->iso_code);
            $oneClickOrderCartEstimate->setCartAmount($cart_amount_oyst);
        }

        $this->logger->info(
            sprintf(
                'New notification oneClickOrderCartEstimate [%s]',
                $oneClickOrderCartEstimate->toJson()
            )
        );

        return $oneClickOrderCartEstimate->toJson();
    }

    /**
     * Return reference by carrier
     * @param  int $id_carrier
     * @return int $id_reference
     */
    public function getReferenceCarrier($id_carrier)
    {
        $id_reference = Db::getInstance()->getValue(
            'SELECT `id_reference`
            FROM `'._DB_PREFIX_.'carrier`
            WHERE id_carrier = '.(int)$id_carrier
        );

        return $id_reference;
    }

    public function deleteCustomerFakeAddress($id_customer)
    {
        $customer = new Customer($id_customer);

        // Get all customer address
        $addresses = $customer->getAddresses($this->context->language->id);

        // Search for john doe name
        foreach ($addresses as $address) {
            if ($address['alias'] == 'OystAddress' && $address['firstname'] == 'John' && $address['lastname'] == 'Doe') {
                $address_obj = new Address($address['id_address']);
                $address_obj->delete();
            }
        }
    }
}
