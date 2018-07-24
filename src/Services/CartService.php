<?php

namespace Oyst\Services;

use Address;
use Carrier;
use Cart;
use Combination;
use Context;
use Country;
use Currency;
use Customer;
use Db;
use Exception;
use Gender;
use Language;
use Message;
use Oyst\Classes\Notification;
use Pack;
use Product;
use Shop;
use Validate;
use Warehouse;

class CartService {

    private $id_lang;
    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CartService();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->id_lang = Language::getIdByIso('FR');
    }

    private function __clone() {}

    public function getCart($id_cart)
    {
        $response = array();
        $cart = new Cart((int)$id_cart);
        if (!empty($cart->id_lang)) {
            $this->id_lang = $cart->id_lang;
        }

        if (Validate::isLoadedObject($cart)) {
            $taxes = array();
            try {
                $context = Context::getContext();

                $response['id_oyst'] = Notification::getOystIdByCartId($cart->id);
                $response['internal_id'] = $cart->id;
                $response['ip'] = CustomerService::getInstance()->getLastIpFromIdCustomer($cart->id_customer);

                //Customer
                $response['user'] = array();
                if (!empty($cart->id_customer)) {
                    $customer = new Customer($cart->id_customer);
                    if (Validate::isLoadedObject($customer)) {
                        $gender = new Gender($customer->id_gender, $this->id_lang);
                        $response['user'] = array(
                            'email' => $customer->email,
                            'firstname' => $customer->firstname,
                            'lastname' => $customer->lastname,
                            'id_oyst' => \Oyst\Classes\Customer::getIdOystFromIdCustomer($customer->id),
                            'gender' => $gender->name,
                            'newsletter' => $customer->newsletter,
                            'birthday' => $customer->birthday,
                            'siret' => $customer->siret,
                            'ape' => $customer->ape,
                        );
                    }
                }

                //Products
                $cart_products = $cart->getProductsWithSeparatedGifts();
                $response['items'] = array();

                $response['promotions'] = array(
                    'free_items' => array(),
                    'discounts' => array(),
                    'coupons' => array(),
                );

                foreach ($cart_products as $cart_product) {
                    if (!isset($taxes[$cart_product['rate']])) {
                        $taxes[$cart_product['rate']] = array(
                            'rate' => $cart_product['rate'],
                            'label' => $cart_product['tax_name'],
                            'amount' => 0,
                        );
                    }
                    $taxes[$cart_product['rate']]['amount'] += $cart_product['total_wt'] - $cart_product['total'];

                    if ($cart_product['is_gift']) {
                        $cart_product['oyst_display'] = 'free';
                        $response['promotions']['free_items'][] = $this->formatItem($cart_product);
                    } else {
                        //Pack content
                        if (Pack::isPack($cart_product['id_product'])) {
                            $cart_product['is_pack'] = 1;
                            foreach (Pack::getItems($cart_product['id_product'], $this->id_lang) as $item) {
                                $package_item = $this->productObjectToItem($item);
                                $package_item['total'] = $package_item['price']*$package_item['pack_quantity'];
                                $package_item['total_wt'] = $package_item['price_wt']*$package_item['pack_quantity'];
                                $package_item['cart_quantity'] = $cart_product['quantity']*$package_item['pack_quantity'];
                                $cover = Product::getCover($package_item['id_product']);
                                if (!empty($cover['id_image'])) {
                                    $package_item['id_image'] = $package_item['id_product'].'-'.$cover['id_image'];
                                }
                                $response['packages'][] = $this->formatItem($package_item);
                            }
                        }
                        $response['items'][] = $this->formatItem($cart_product);
                    }
                }

                if (!isset($response['packages'])) {
                    $response['packages'] = array();
                }

                //TODO Check for module like crossselling
                $response['proposal_items'] = array();

                $cart_rules = $cart->getCartRules();

                foreach ($cart_rules as $cart_rule) {
                    $amount_tax_incl = $cart_rule['obj']->getContextualValue(true, $context);
                    $amount_tax_excl = $cart_rule['obj']->getContextualValue(false, $context);
                    if (!empty($amount)) {
                        //discounts
                        if (empty($cart_rule['code'])) {
                            $response['promotions']['discounts'][] = array(
                                'id_discount' => $cart_rule['id_cart_rule'],
                                'label' => $cart_rule['name'],
                                'amount_tax_incl' => $amount_tax_incl,
                                'amount_tax_excl' => $amount_tax_excl,
                            );
                        //Coupons
                        } else {
                            $response['promotions']['coupons'][] = array(
                                'label' => $cart_rule['name'],
                                'code' => $cart_rule['code'],
                                'amount_tax_incl' => $amount_tax_incl,
                                'amount_tax_excl' => $amount_tax_excl,
                            );
                        }
                    }
                }

                //TODO loyalty points
                $response['user_advantages'] = array(
                    'points_fidelity' => array(),
                    'balance' => array(),
                );

                $carriers = array();
                $carriers_errors = array();
                foreach ($cart_products as $cart_product) {
                    $warehouse_list = Warehouse::getProductWarehouseList($cart_product['id_product'], $cart_product['id_product_attribute'], $cart->id_shop);
                    if (count($warehouse_list) == 0) {
                        $warehouse_list = Warehouse::getProductWarehouseList($cart_product['id_product'], $cart_product['id_product_attribute']);
                    }
                    if (empty($warehouse_list)) {
                        $warehouse_list = array(0 => array('id_warehouse' => 0));
                    }

                    //Get availables carriers for cart
                    foreach ($warehouse_list as $warehouse) {
                        $product_carriers = Carrier::getAvailableCarrierList(new Product($cart_product['id_product']), $warehouse['id_warehouse'], $cart->id_address_delivery, $cart->id_shop, $cart, $carriers_errors);
                        if (empty($carriers)) {
                            $carriers = $product_carriers;
                        } else {
                            $carriers = array_intersect($carriers, $product_carriers);
                        }
                    }
                }
                if (!empty($carriers_errors)) {
                    $response['errors'][] = 'Error on carriers recuperation : '.print_r($carriers_errors, true);
                }

                //Shipping

                //Available carriers
                $available_carriers = array();
                foreach ($carriers as $id_carrier) {
                    $carrier_obj = new Carrier($id_carrier, $this->id_lang);
                    if (Validate::isLoadedObject($carrier_obj)) {
                        $available_carriers[] = array(
                            'label' => $carrier_obj->name,
                            'reference' => $carrier_obj->id_reference,
                            'delivery_delay' => $carrier_obj->delay,
                            'amount_tax_incl' => $cart->getCarrierCost($id_carrier, true),
                            'amount_tax_excl' => $cart->getCarrierCost($id_carrier, false),
                        );
                    }
                }

                $response['shipping'] = array(
                    'address' => array(),
                    'methods_available' => $available_carriers,
                    'method_applied' => array(),
                );

                //Applied carrier
                if (!empty($cart->id_carrier)) {
                    $selected_carrier_obj = new Carrier($cart->id_carrier, $this->id_lang);
                    if (Validate::isLoadedObject($selected_carrier_obj)) {
                        $response['shipping']['method_applied'] = array(
                            'label' => $selected_carrier_obj->name,
                            'reference' => $selected_carrier_obj->id_reference,
                            'delivery_delay' => $selected_carrier_obj->delay,
                            'amount_tax_incl' => $cart->getCarrierCost($cart->id_carrier, true),
                            'amount_tax_excl' => $cart->getCarrierCost($cart->id_carrier, false),
                        );
                    }
                }

                //Addresses
                if (!empty($cart->id_address_delivery)) {
                    $address = new Address($cart->id_address_delivery);
                    if (Validate::isLoadedObject($address)) {
                        $response['shipping']['address'] = $this->formatAddress($address);
                    }
                }
                $response['shipping']['amount_tax_incl'] = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

                $response['billing'] = array(
                    'address' => array(),
                );
                if (!empty($cart->id_address_invoice)) {
                    $address = new Address($cart->id_address_invoice);
                    if (Validate::isLoadedObject($address)) {
                        $response['billing']['address'] = $this->formatAddress($address);
                        if (!empty($response['user'])) {
                            $response['user']['phone_mobile'] = (!empty($response['billing']['address']['phone_mobile']) ? $response['billing']['address']['phone_mobile'] : $response['billing']['address']['phone']);
                        }
                    }
                }

                //Shop
                $response['shop'] = array();
                $shop_obj = new Shop($cart->id_shop);
                if (Validate::isLoadedObject($shop_obj)) {
                    $response['shop'] = array(
                        'label' => $shop_obj->name,
                        'code' => $shop_obj->id,
                        'url' => $shop_obj->getBaseURL(),
                    );
                }

                //Totals
                $response['totals'] = array(
                    'tax_incl' => array(
                        'total_items' => $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS),
                        'total_shipping' => $cart->getOrderTotal(true, Cart::ONLY_SHIPPING),
                        'total_discount' => $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS),
                        'total' => $cart->getOrderTotal(true, Cart::BOTH),
                    ),
                    'tax_excl' => array(
                        'total_items' => $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS),
                        'total_shipping' => $cart->getOrderTotal(false, Cart::ONLY_SHIPPING),
                        'total_discount' => $cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS),
                        'total' => $cart->getOrderTotal(false, Cart::BOTH),
                    ),
                    //TODO Separate taxes by tax rate
                    'taxes' => array(
                        array(
                            "label" => "TVA total",
                            "amount" => $cart->getOrderTotal(true, Cart::BOTH)-$cart->getOrderTotal(false, Cart::BOTH),
                            "rate" => "20"
                        ),
                    ),
                );

                //Currency
                $currency = new Currency($cart->id_currency);
                $response['currency'] = $currency->iso_code;

                //Message
                $order_message = Message::getMessageByCartId($cart->id);
                $response['message'] = array(
                    array(
                        'type' => 'gift',
                        'content' => $cart->gift_message,
                    ),
                    array(
                        'type' => 'order',
                        'content' => $order_message['message'],
                    ),
                );

                //TODO Checkout agreements
                $response['checkout_agreements'] = array(
                    'acceptance_message' => '',
                    'full_agreements' => '',
                );

                $response['context'] = array();

            } catch(Exception $e) {
                $response['errors'][] = $e->getMessage();
            }
        } else {
            $response['errors'][] = 'Bad id_cart';
        }
        return $response;
    }

    /**
     * @param Product $product_obj
     * @return array
     */
    public function productObjectToItem(Product $product_obj)
    {
        if (is_object($product_obj)) {
            $item_formated = json_decode(json_encode($product_obj), true);
            //Define fields for formatItem compatibility
            $item_formated['id_product'] = $item_formated['id'];
            $item_formated['id_product_attribute'] = 0;
            $item_formated['price_wt'] = Product::getPriceStatic($item_formated['id_product'], true);
            $item_formated['price_without_reduction'] = Product::getPriceStatic($item_formated['id_product'], false, null, 6, null, false, false);
            $item_formated['price_without_reduction_wt'] = Product::getPriceStatic($item_formated['id_product'], true, null, 6, null, false, false);
            $item_formated['total'] = $item_formated['price'];
            $item_formated['total_wt'] = $item_formated['price_wt'];
            $item_formated['quantity_available'] = $item_formated['quantity'];
            $item_formated['rate'] = $product_obj->getTaxesRate();
        } else {
            $item_formated = $product_obj;
        }
        return $item_formated;
    }

    /**
     * @param Address $address
     * @return array
     */
    public function formatAddress(Address $address)
    {
        return array(
            'alias' => $address->alias,
            'company' => $address->company,
            'lastname' => $address->lastname,
            'firstname' => $address->firstname,
            'street1' => $address->address1,
            'street2' => $address->address2,
            'postcode' => $address->postcode,
            'city' => $address->city,
            'country' => array(
                'code' => Country::getIsoById($address->id_country),
                'label' => Country::getNameById($this->id_lang, $address->id_country),
            ),
            'other' => $address->other,
            'phone' => $address->phone,
            'phone_mobile' => $address->phone_mobile,
            'vat_number' => $address->vat_number,
            'dni' => $address->dni,
        );
    }

    /**
     * @param $item
     * @return array
     */
    public function formatItem($item)
    {
        if (isset($item['price_without_reduction_wt'])) {
            $price_without_discount_tax_incl = $item['price_without_reduction_wt'];
        } else {
            $price_without_discount_tax_incl = $item['price_without_reduction']*(1+ $item['rate']/100);
        }
        $image = Context::getContext()->link->getImageLink($item['link_rewrite'], $item['id_image']);

        //Get customizations
//        if (!empty($item_formated['id_customization'])) {
//            $customizations = $cart->getProductCustomization($item_formated['id_product']);
//            foreach ($customizations as &$customization) {
//                if ($customization['type'] == Product::CUSTOMIZE_FILE) {
//                    $customization['type_name'] = 'file';
//                    $customization['value'] = Tools::getShopDomainSsl(true).'/upload/'.$customization['value'];
//                } elseif ($customization['type'] == Product::CUSTOMIZE_TEXTFIELD) {
//                    $customization['type_name'] = 'textfield';
//                } else {
//                    $customization['type_name'] = 'undefined';
//                }
//            }
//            $item_formated['customizations'] = $customizations;
//        }

        $product_type = 'simple';
        if (isset($item['is_virtual']) && $item['is_virtual']) {
            $product_type = 'virtual';
        }
        if (isset($item['is_pack']) && $item['is_pack']) {
            $product_type = 'bundle';
        }

        $oyst_display = 'normal';
        if (!empty($item['oyst_display'])) {
            $oyst_display = $item['oyst_display'];
        }

        $attributes_variant = array();
        if (!empty($item['id_product_attribute'])) {
            $attributes = Db::getInstance()->executeS("SELECT al.`id_attribute`, al.`name` value_name, agl.`public_name` attribute_name 
                FROM "._DB_PREFIX_."product_attribute_combination pac
                INNER JOIN "._DB_PREFIX_."attribute a ON a.id_attribute = pac.id_attribute
                INNER JOIN "._DB_PREFIX_."attribute_lang al ON (pac.id_attribute = al.id_attribute AND al.id_lang=".$this->id_lang.")
                INNER JOIN "._DB_PREFIX_."attribute_group_lang agl ON (a.id_attribute_group = agl.id_attribute_group AND agl.id_lang=".$this->id_lang.")
                WHERE pac.id_product_attribute=".$item['id_product_attribute']);

            if (!empty($attributes)) {
                foreach ($attributes as $attribute) {
                    $attributes_variant[] = array(
                        'code' => $attribute['id_attribute'],
                        'label' => $attribute['attribute_name'].' : '.$attribute['value_name']
                    );
                }
            }
            $product_type = 'configurable';
        }

        return array(
            'reference' => $item['reference'],
            'internal_reference' => $item['id_product'].'-'.$item['id_product_attribute'],
            'attributes_variant' => $attributes_variant,
            'quantity' => $item['cart_quantity'],
            'quantity_available' => $item['quantity_available'],
            'quantity_minimal' => $item['minimal_quantity'],
            'name' => $item['name'],
            'type' => $product_type, //"simple", "configurable", "virtual", "downloadable", "bundle"},
            'description_short' => $item['description_short'],
            'availability_status' => '',//{enum  => "now", "later"},
            'availability_date' => '',
            'availability_label' => '',
            'price' => array(
                'tax_excl' => $item['price'],
                'tax_incl' => $item['price_wt'],
                'without_discount_tax_excl' => $item['price_without_reduction'],
                'without_discount_tax_incl' => $price_without_discount_tax_incl,
                'total_tax_excl' => $item['total'],
                'total_tax_incl' => $item['total_wt'],
            ),
            'width' => $item['width'],
            'height' => $item['height'],
            'depth' => $item['depth'],
            'weight' => $item['weight'],
            'tax_rate' => $item['rate'],
            'tax_name' => $item['tax_name'],
            'image' => $image,
            'user_input' => array(
//                array(
//                    'key' => '',
//                    'value' => '',
//                ),
            ),
            'oyst_display' => $oyst_display,
            'discounts' => array(
//                array(
//                    'id' => 0,
//                    'label' => '',
//                    'amount_tax_incl' => 0,
//                    'amount_tax_excl' => 0,
//                ),
            ),
            'child_items' => array()
        );
    }
}
