<?php

namespace Oyst\Classes;

use Address;
use Country;
use Pack;
use Product;
use ProductDownload;

abstract class AbstractBuilder
{
    protected $id_lang;

    public function __construct($id_lang)
    {
        $this->id_lang = $id_lang;
    }

    protected function getUser($customer, $gender_name, $address_invoice)
    {
        $user = array();

        if (!empty($customer)) {
            if (!empty($address_invoice)) {
                $phone_mobile = (!empty($address_invoice->phone_mobile) ? $address_invoice->phone_mobile : $address_invoice->phone);
            } else {
                $phone_mobile = '06000000';
            }
            $user = array(
                'email' => $customer->email,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'phone_mobile' => $phone_mobile,
                'id_oyst' => \Oyst\Classes\OystCustomer::getIdOystFromIdCustomer($customer->id),
                'gender' => $gender_name,
                'newsletter' => (bool)$customer->newsletter,
                'birthday' => $customer->birthday,
                'siret' => $customer->siret,
                'ape' => $customer->ape,
            );
        }
        return $user;
    }

    protected function getItems($products)
    {
        $items = array();
        foreach ($products as $product) {
            if (!isset($taxes[$product['rate']])) {
                $taxes[$product['rate']] = array(
                    'rate' => (float)$product['rate'],
                    'label' => $product['tax_name'],
                    'amount' => 0,
                );
            }
            $taxes[$product['rate']]['amount'] += $product['total_wt'] - $product['total'];

            if ($product['is_gift']) {
                $product['oyst_display'] = 'free';
                $items[] = $this->formatItem($product);
            } else {
                //Pack content
                $child_items = array();
                if (Pack::isPack($product['id_product'])) {
                    $product['is_pack'] = 1;
                    foreach (Pack::getItems($product['id_product'], $this->id_lang) as $item) {
                        $package_item = $this->productObjectToItem($item);
                        $package_item['total'] = $package_item['price']*$package_item['pack_quantity'];
                        $package_item['total_wt'] = $package_item['price_wt']*$package_item['pack_quantity'];
                        $package_item['cart_quantity'] = $product['quantity']*$package_item['pack_quantity'];
                        $cover = Product::getCover($package_item['id_product']);
                        if (!empty($cover['id_image'])) {
                            $package_item['id_image'] = $package_item['id_product'].'-'.$cover['id_image'];
                        }
                        $child_items[] = $this->formatItem($package_item);
                    }
                }
                $formatted_item = $this->formatItem($product);
                $formatted_item['child_items'] = $child_items;
                $items[] = $formatted_item;
            }
        }
        return $items;
    }

    protected function getDiscounts($cart_rules, $context)
    {
        $discounts = array();
        if (!empty($cart_rules)) {
            foreach ($cart_rules as $cart_rule) {
                if ((!empty($cart_rule['obj']->reduction_amount) || !empty($cart_rule['obj']->reduction_percent)) && empty($cart_rule['code'])) {
                    $amount_tax_incl = $cart_rule['obj']->getContextualValue(true, $context);
                    $amount_tax_excl = $cart_rule['obj']->getContextualValue(false, $context);
                    $discounts[] = array(
                        'id_discount' => $cart_rule['id_cart_rule'],
                        'label' => $cart_rule['name'],
                        'amount_tax_incl' => (float)$amount_tax_incl,
                        'amount_tax_excl' => (float)$amount_tax_excl,
                    );
                }
            }
        }
        return $discounts;
    }

    protected function getCoupons($cart_rules, $context)
    {
        $coupons = array();
        if (!empty($cart_rules)) {
            foreach ($cart_rules as $cart_rule) {
                if ((!empty($cart_rule['obj']->reduction_amount) || !empty($cart_rule['obj']->reduction_percent)) && !empty($cart_rule['code'])) {
                    $amount_tax_incl = $cart_rule['obj']->getContextualValue(true, $context);
                    $amount_tax_excl = $cart_rule['obj']->getContextualValue(false, $context);
                    $coupons[] = array(
                        'label' => $cart_rule['name'],
                        'code' => $cart_rule['code'],
                        'amount_tax_incl' => (float)$amount_tax_incl,
                        'amount_tax_excl' => (float)$amount_tax_excl,
                    );
                }
            }
        }
        return $coupons;
    }

    protected function getAvailableCarriers($carriers, $cart)
    {
        $available_carriers = array();
        if (!empty($carriers)) {
            foreach ($carriers as $carrier) {
                $available_carriers[] = $this->formatCarrier($carrier, $cart);
            }
        }
        return $available_carriers;
    }

    protected function getSelectedCarrier($carrier, $cart)
    {
        $selected_carrier = array();
        if (!empty($carrier)) {
            $selected_carrier = $this->formatCarrier($carrier, $cart);
        }
        return $selected_carrier;
    }

    protected function getShop($shop)
    {
        $result = array();
        if (!empty($result)) {
            $result = array(
                'label' => $shop->name,
                'code' => $shop->id,
                'url' => $shop->getBaseURL(),
            );
        }
        return $result;
    }

    protected function formatCarrier($carrier, $cart)
    {
        return array(
            'label' => $carrier->name,
            'reference' => $carrier->id_reference,
            'amount_tax_incl' => (float)$cart->getPackageShippingCost($carrier->id, true, null, null, Address::getZoneById($cart->id_address_delivery)),
            'amount_tax_excl' => (float)$cart->getPackageShippingCost($carrier->id, false, null, null, Address::getZoneById($cart->id_address_delivery)),
        );
    }

    /**
     * @param Product $product_obj
     * @return array
     */
    protected function productObjectToItem($product_obj)
    {
        if (is_object($product_obj)) {
            $item_formated = json_decode(json_encode($product_obj), true);
            //Define fields for formatItem compatibility
            $item_formated['id_product'] = (int)$item_formated['id'];
            $item_formated['id_product_attribute'] = 0;
            $item_formated['price_wt'] = (float)Product::getPriceStatic($item_formated['id_product'], true);
            $item_formated['price_without_reduction'] = (float)Product::getPriceStatic($item_formated['id_product'], false, null, 6, null, false, false);
            $item_formated['price_without_reduction_wt'] = (float)Product::getPriceStatic($item_formated['id_product'], true, null, 6, null, false, false);
            $item_formated['total'] = (float)$item_formated['price'];
            $item_formated['total_wt'] = (float)$item_formated['price_wt'];
            $item_formated['quantity_available'] = (int)$item_formated['quantity'];
            $item_formated['rate'] = (float)$product_obj->getTaxesRate();
        } else {
            $item_formated = $product_obj;
        }
        return $item_formated;
    }

    /**
     * @param Address $address
     * @return array
     */
    protected function formatAddress($address)
    {
        if (empty($address)) {
            return array();
        }
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
    protected function formatItem($item)
    {
        $price_without_discount_tax_incl = 0;
        $price_without_discount_tax_excl = 0;
        if (isset($item['price_without_reduction_wt'])) {
            $price_without_discount_tax_incl = $item['price_without_reduction_wt'];
        }

        if (isset($item['price_without_reduction'])) {
            $price_without_discount_tax_excl = $item['price_without_reduction'];
            if (empty($price_without_discount_tax_incl)) {
                $price_without_discount_tax_incl = $price_without_discount_tax_excl*(1+ $item['rate']/100);
            }
        }

        $user_inputs = array();
        //Format customizations
        if (!empty($item['customizations'])) {
            foreach ($item['customizations'] as $customization) {
                $user_inputs[] = array(
                    'key' => $customization['id_customization'].'-'.$customization['index'],
                    'value' => $customization['value'],
                );
            }
        }

        $product_type = 'simple';
        if (isset($item['is_virtual']) && $item['is_virtual']) {
            $product_type = 'virtual';
            if (ProductDownload::getIdFromIdProduct($item['id_product'], false)) {
                $product_type = 'downloadable';
            }
        }
        if (isset($item['is_pack']) && $item['is_pack']) {
            $product_type = 'bundle';
        }

        $oyst_display = 'normal';
        if (!empty($item['oyst_display'])) {
            $oyst_display = $item['oyst_display'];
        }

        $attributes_variant = array();

        if (!empty($item['attributes'])) {
            foreach ($item['attributes'] as $attribute) {
                $attributes_variant[] = array(
                    'code' => $attribute['id_attribute'],
                    'label' => $attribute['attribute_name'],
                    'value' => $attribute['value_name']
                );
            }
            $product_type = 'variant';
        }

        return array(
            'reference' => $item['reference'],
            'internal_reference' => $item['id_product'].'-'.$item['id_product_attribute'],
            'attributes_variant' => $attributes_variant,
            'quantity' => (int)$item['cart_quantity'],
            'quantity_available' => (int)$item['quantity_available'],
            'quantity_minimal' => (int)$item['minimal_quantity'],
            'name' => $item['name'],
            'type' => $product_type, //"simple", "variant", "virtual", "downloadable", "bundle"},
            'description_short' => $item['description_short'],
            'availability_status' => '',//{enum  => "now", "later"},
            'availability_date' => '',
            'availability_label' => '',
            'price' => array(
                'tax_excl' => round($item['price'], 2),
                'tax_incl' => round($item['price_wt'], 2),
                'without_discount_tax_excl' => round($price_without_discount_tax_excl, 2),
                'without_discount_tax_incl' => round($price_without_discount_tax_incl, 2),
                'total_tax_excl' => round($item['total'], 2),
                'total_tax_incl' => round($item['total_wt'], 2),
            ),
            'width' => $item['width'],
            'height' => $item['height'],
            'depth' => $item['depth'],
            'weight' => $item['weight'],
            'tax_rate' => (float)$item['rate'],
            'tax_name' => $item['tax_name'],
            'image' => $item['image'],
            'user_input' => $user_inputs,
            'oyst_display' => $oyst_display,
            'discounts' => array(
//                array(
//                    'id' => 0,
//                    'label' => '',
//                    'amount_tax_incl' => 0,
//                    'amount_tax_excl' => 0,
//                ),
            ),
            'child_items' => array(),
        );
    }
}
