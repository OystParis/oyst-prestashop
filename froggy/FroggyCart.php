<?php

/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't buy this module on Froggy-Commerce.com, ThemeForest.net
 * or Addons.PrestaShop.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod
 * @license   Unauthorized copying of this file, via any medium is strictly prohibited
 */
use Oyst\Repository\OneClickShipmentRepository;

class FroggyCart extends Cart {

    /**
     * Return package shipping cost
     *
     * @param int          $id_carrier      Carrier ID (default : current carrier)
     * @param bool         $use_tax
     * @param Country|null $default_country
     * @param array|null   $product_list    List of product concerned by the shipping.
     *                                      If null, all the product of the cart are used to calculate the shipping cost
     * @param int|null $id_zone
     *
     * @return float Shipping total
     */
    public function getPackageShippingCostOyst($id_carrier = null, $use_tax = true, Country $default_country = null, $product_list = null, $id_zone = null, $payment_method = null) {
        if (Module::isInstalled('oyst') || Module::isEnabled('oyst')) {
            if ($payment_method != null && preg_match('/OneClick/i', $payment_method)) {
                $oneClickShipmentRepository = new OneClickShipmentRepository(Db::getInstance());
                if (empty($id_carrier)) {
                    $id_carrier = Configuration::get('PS_CARRIER_DEFAULT');
                }
                $carrier = new Carrier($id_carrier);

                $shipment = $oneClickShipmentRepository->getShipment($id_carrier);
                $total_product_without_taxes = $this->getOrderTotal(true, Cart::ONLY_PRODUCTS);

                $shipping_cost_with_tax = 0;
                $shipping_cost_whitout_tax = 0;
                $first_product = true;

                if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice') {
                    $address_id = (int) $this->id_address_invoice;
                } elseif (count($product_list)) {
                    $prod = current($product_list);
                    $address_id = (int) $prod['id_address_delivery'];
                } else {
                    $address_id = null;
                }
                if (!Address::addressExists($address_id)) {
                    $address_id = null;
                }

                if (!Tax::excludeTaxeOption()) {
                    $address = Address::initialize((int) $address_id);

                    if (Configuration::get('PS_ATCP_SHIPWRAP')) {
                        $carrier_tax = 0;
                    } else {
                        $carrier_tax = $carrier->getTaxesRate($address);
                    }
                }

                // Check free shipping for order
                if ($total_product_without_taxes < $shipment['free_shipping']) {
                    foreach ($product_list as $product) {
                        // Check first product
                        if ($first_product) {
                            $qty_first_product = $product['cart_quantity'];
                            $shipping_cost_with_tax += $shipment['amount_leader'];
                            if ($qty_first_product > 1) {
                                for ($i = 1; $i < $qty_first_product; $i++) {
                                    $shipping_cost_with_tax += $shipment['amount_follower'];
                                }
                            }
                            $first_product = false;
                        } else {
                            $shipping_cost_with_tax = $shipping_cost_with_tax + ($product['cart_quantity'] * $shipment['amount_follower']);
                        }
                    }

                    if (isset($carrier_tax)) {
                        $shipping_cost_without_tax = $shipping_cost_with_tax * (1 - ($carrier_tax / 100));
                    }
                }

                if ($use_tax)
                    return (float) $shipping_cost_with_tax;
                else
                    return (float) $shipping_cost_without_tax;
            } else {
                return parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_zone, $payment_method);
            }
        } else {
            return parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_zone, $payment_method);
        }
    }

    public function getTotalShippingCostOyst($delivery_option = null, $use_tax = true, Country $default_country = null, $payment_method = null) {
        if (Module::isInstalled('oyst') || Module::isEnabled('oyst')) {
            if ($payment_method != null && preg_match('/OneClick/i', $payment_method)) {
                $oneClickShipmentRepository = new OneClickShipmentRepository(Db::getInstance());
                $context = Context::getContext();
                $id_carrier = (int) $context->cart->id_carrier;
                $shipping_cost_with_tax = 0;
                $shipping_cost_without_tax = 0;
                $first_product = true;

                if ($id_carrier) {
                    $shipment = $oneClickShipmentRepository->getShipment($id_carrier);
                    $products = $context->cart->getProducts();
                    $total_product = $context->cart->getOrderTotal($use_tax, Cart::ONLY_PRODUCTS);
                    if ($total_product < $shipment['free_shipping']) {
                        foreach ($products as $product) {
                            if ($first_product) {
                                $qty_first_product = $product['cart_quantity'];
                                $shipping_cost_with_tax += $shipment['amount_leader'];
                                if ($qty_first_product > 1) {
                                    for ($i = 1; $i < $qty_first_product; $i++) {
                                        $shipping_cost_with_tax += $shipment['amount_follower'];
                                    }
                                }
                                $first_product = false;
                            } else {
                                $shipping_cost_with_tax = $shipping_cost_with_tax + ($product['cart_quantity'] * $shipment['amount_follower']);
                            }
                        }
                    }
                }

                if ($use_tax)
                    return $shipping_cost_with_tax;
                else
                    return $shipping_cost_without_tax;
            } else {
                return parent::getTotalShippingCost($delivery_option, $use_tax, $default_country);
            }
        } else {
            return parent::getTotalShippingCost($delivery_option, $use_tax, $default_country);
        }
    }

    /**
     * This function returns the total cart amount
     *
     * Possible values for $type:
     * Cart::ONLY_PRODUCTS
     * Cart::ONLY_DISCOUNTS
     * Cart::BOTH
     * Cart::BOTH_WITHOUT_SHIPPING
     * Cart::ONLY_SHIPPING
     * Cart::ONLY_WRAPPING
     * Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING
     * Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING
     *
     * @param bool $withTaxes With or without taxes
     * @param int $type Total type
     * @param bool $use_cache Allow using cache of the method CartRule::getContextualValue
     * @return float Order total
     */
    public function getOrderTotalOyst($with_taxes = true, $type = Cart::BOTH, $products = null, $id_carrier = null, $use_cache = true, $payment_method = null) {
        // Dependencies
        if (version_compare(_PS_VERSION_, '1.6') >= 0){
            $address_factory = Adapter_ServiceLocator::get('Adapter_AddressFactory');
            $price_calculator = Adapter_ServiceLocator::get('Adapter_ProductPriceCalculator');
            $configuration = Adapter_ServiceLocator::get('Core_Business_ConfigurationInterface');
        
            $ps_tax_address_type = $configuration->get('PS_TAX_ADDRESS_TYPE');
            $ps_use_ecotax = $configuration->get('PS_USE_ECOTAX');
            $ps_round_type = $configuration->get('PS_ROUND_TYPE');
            $ps_ecotax_tax_rules_group_id = $configuration->get('PS_ECOTAX_TAX_RULES_GROUP_ID');
            $compute_precision = ((version_compare(_PS_VERSION_, '1.6') < 0) ? 2 : $configuration->get('_PS_PRICE_COMPUTE_PRECISION_'));

            if (!$this->id) {
                return 0;
            }

            $type = (int) $type;
            $array_type = array(
                Cart::ONLY_PRODUCTS,
                Cart::ONLY_DISCOUNTS,
                Cart::BOTH,
                Cart::BOTH_WITHOUT_SHIPPING,
                Cart::ONLY_SHIPPING,
                Cart::ONLY_WRAPPING,
                Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
                Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
            );

            // Define virtual context to prevent case where the cart is not the in the global context
            $virtual_context = Context::getContext()->cloneContext();
            $virtual_context->cart = $this;

            if (!in_array($type, $array_type)) {
                die(Tools::displayError());
            }

            $with_shipping = in_array($type, array(Cart::BOTH, Cart::ONLY_SHIPPING));

            // if cart rules are not used
            if ($type == Cart::ONLY_DISCOUNTS && !CartRule::isFeatureActive()) {
                return 0;
            }

            // no shipping cost if is a cart with only virtuals products
            $virtual = $this->isVirtualCart();
            if ($virtual && $type == Cart::ONLY_SHIPPING) {
                return 0;
            }

            if ($virtual && $type == Cart::BOTH) {
                $type = Cart::BOTH_WITHOUT_SHIPPING;
            }

            if ($with_shipping || $type == Cart::ONLY_DISCOUNTS) {
                if (is_null($products) && is_null($id_carrier)) {
                    if (Module::isInstalled('oyst') || Module::isEnabled('oyst')) {
                        if ($payment_method != null && preg_match('/OneClick/i', $payment_method)) {
                            $shipping_fees = $this->getTotalShippingCostOyst(null, (bool) $with_taxes, null, $payment_method);
                        } else {
                            $shipping_fees = $this->getTotalShippingCost(null, (bool) $with_taxes);
                        }
                    } else {
                        $shipping_fees = $this->getTotalShippingCost(null, (bool) $with_taxes);
                    }
                } else {
                    if (Module::isInstalled('oyst') || Module::isEnabled('oyst')) {
                        if ($payment_method != null && preg_match('/OneClick/i', $payment_method)) {
                            $shipping_fees = $this->getPackageShippingCostOyst((int) $id_carrier, (bool) $with_taxes, null, $products, null, $payment_method);
                        } else {
                            $shipping_fees = $this->getPackageShippingCost((int) $id_carrier, (bool) $with_taxes, null, $products);
                        }
                    } else {
                        $shipping_fees = $this->getPackageShippingCost((int) $id_carrier, (bool) $with_taxes, null, $products);
                    }
                }
            } else {
                $shipping_fees = 0;
            }

            if ($type == Cart::ONLY_SHIPPING) {
                return $shipping_fees;
            }

            if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING) {
                $type = Cart::ONLY_PRODUCTS;
            }

            $param_product = true;
            if (is_null($products)) {
                $param_product = false;
                $products = $this->getProducts();
            }

            if ($type == Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING) {
                foreach ($products as $key => $product) {
                    if ($product['is_virtual']) {
                        unset($products[$key]);
                    }
                }
                $type = Cart::ONLY_PRODUCTS;
            }

            $order_total = 0;
            if (Tax::excludeTaxeOption()) {
                $with_taxes = false;
            }

            $products_total = array();
            $ecotax_total = 0;

            if (version_compare(_PS_VERSION_, '1.6') < 0) {
                foreach ($products as $product) { // products refer to the cart details
                    if ($virtual_context->shop->id != $product['id_shop'])
                        $virtual_context->shop = new Shop((int) $product['id_shop']);

                    if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice')
                        $address_id = (int) $this->id_address_invoice;
                    else
                        $address_id = (int) $product['id_address_delivery']; // Get delivery address of the product from the cart
                    if (!Address::addressExists($address_id))
                        $address_id = null;

                    if ($this->_taxCalculationMethod == PS_TAX_EXC) {
                        // Here taxes are computed only once the quantity has been applied to the product price
                        $price = Product::getPriceStatic(
                                        (int) $product['id_product'], false, (int) $product['id_product_attribute'], 2, null, false, true, $product['cart_quantity'], false, (int) $this->id_customer ? (int) $this->id_customer : null, (int) $this->id, $address_id, $null, true, true, $virtual_context
                        );

                        $total_ecotax = $product['ecotax'] * (int) $product['cart_quantity'];
                        $total_price = $price * (int) $product['cart_quantity'];

                        if ($with_taxes) {
                            $product_tax_rate = (float) Tax::getProductTaxRate((int) $product['id_product'], (int) $address_id, $virtual_context);
                            $product_eco_tax_rate = Tax::getProductEcotaxRate((int) $address_id);

                            $total_price = ($total_price - $total_ecotax) * (1 + $product_tax_rate / 100);
                            $total_ecotax = $total_ecotax * (1 + $product_eco_tax_rate / 100);
                            $total_price = Tools::ps_round($total_price + $total_ecotax, 2);
                        }
                    } else {
                        if ($with_taxes)
                            $price = Product::getPriceStatic(
                                            (int) $product['id_product'], true, (int) $product['id_product_attribute'], 2, null, false, true, $product['cart_quantity'], false, ((int) $this->id_customer ? (int) $this->id_customer : null), (int) $this->id, ((int) $address_id ? (int) $address_id : null), $null, true, true, $virtual_context
                            );
                        else
                            $price = Product::getPriceStatic(
                                            (int) $product['id_product'], false, (int) $product['id_product_attribute'], 2, null, false, true, $product['cart_quantity'], false, ((int) $this->id_customer ? (int) $this->id_customer : null), (int) $this->id, ((int) $address_id ? (int) $address_id : null), $null, true, true, $virtual_context
                            );

                        $total_price = Tools::ps_round($price * (int) $product['cart_quantity'], 2);
                    }
                    $order_total += $total_price;
                }
            } else {
                foreach ($products as $product) {
                    // products refer to the cart details

                    if ($virtual_context->shop->id != $product['id_shop']) {
                        $virtual_context->shop = new Shop((int) $product['id_shop']);
                    }

                    if ($ps_tax_address_type == 'id_address_invoice') {
                        $id_address = (int) $this->id_address_invoice;
                    } else {
                        $id_address = (int) $product['id_address_delivery'];
                    } // Get delivery address of the product from the cart
                    if (!$address_factory->addressExists($id_address)) {
                        $id_address = null;
                    }

                    // The $null variable below is not used,
                    // but it is necessary to pass it to getProductPrice because
                    // it expects a reference.
                    $null = null;
                    $price = $price_calculator->getProductPrice(
                            (int) $product['id_product'], $with_taxes, (int) $product['id_product_attribute'], 6, null, false, true, $product['cart_quantity'], false, (int) $this->id_customer ? (int) $this->id_customer : null, (int) $this->id, $id_address, $null, $ps_use_ecotax, true, $virtual_context
                    );

                    $address = $address_factory->findOrCreate($id_address, true);

                    if ($with_taxes) {
                        $id_tax_rules_group = Product::getIdTaxRulesGroupByIdProduct((int) $product['id_product'], $virtual_context);
                        $tax_calculator = TaxManagerFactory::getManager($address, $id_tax_rules_group)->getTaxCalculator();
                    } else {
                        $id_tax_rules_group = 0;
                    }

                    if (in_array($ps_round_type, array(Order::ROUND_ITEM, Order::ROUND_LINE))) {
                        if (!isset($products_total[$id_tax_rules_group])) {
                            $products_total[$id_tax_rules_group] = 0;
                        }
                    } elseif (!isset($products_total[$id_tax_rules_group . '_' . $id_address])) {
                        $products_total[$id_tax_rules_group . '_' . $id_address] = 0;
                    }

                    switch ($ps_round_type) {
                        case Order::ROUND_TOTAL:
                            $products_total[$id_tax_rules_group . '_' . $id_address] += $price * (int) $product['cart_quantity'];
                            break;

                        case Order::ROUND_LINE:
                            $product_price = $price * $product['cart_quantity'];
                            $products_total[$id_tax_rules_group] += Tools::ps_round($product_price, $compute_precision);
                            break;

                        case Order::ROUND_ITEM:
                        default:
                            $product_price = $price;
                            $products_total[$id_tax_rules_group] += Tools::ps_round($product_price, $compute_precision) * (int) $product['cart_quantity'];
                            break;
                    }
                }

                foreach ($products_total as $key => $price) {
                    $order_total += $price;
                }
            }

            $order_total_products = $order_total;

            if ($type == Cart::ONLY_DISCOUNTS) {
                $order_total = 0;
            }

            // Wrapping Fees
            $wrapping_fees = 0;

            // With PS_ATCP_SHIPWRAP on the gift wrapping cost computation calls getOrderTotal with $type === Cart::ONLY_PRODUCTS, so the flag below prevents an infinite recursion.
            $include_gift_wrapping = (!$configuration->get('PS_ATCP_SHIPWRAP') || $type !== Cart::ONLY_PRODUCTS);

            if ($this->gift && $include_gift_wrapping) {
                $wrapping_fees = Tools::convertPrice(Tools::ps_round($this->getGiftWrappingPrice($with_taxes), $compute_precision), Currency::getCurrencyInstance((int) $this->id_currency));
            }
            if ($type == Cart::ONLY_WRAPPING) {
                return $wrapping_fees;
            }

            $order_total_discount = 0;
            $order_shipping_discount = 0;
            if (!in_array($type, array(Cart::ONLY_SHIPPING, Cart::ONLY_PRODUCTS)) && CartRule::isFeatureActive()) {
                // First, retrieve the cart rules associated to this "getOrderTotal"
                if ($with_shipping || $type == Cart::ONLY_DISCOUNTS) {
                    $cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_ALL);
                } else {
                    $cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_REDUCTION);
                    // Cart Rules array are merged manually in order to avoid doubles
                    foreach ($this->getCartRules(CartRule::FILTER_ACTION_GIFT) as $tmp_cart_rule) {
                        $flag = false;
                        foreach ($cart_rules as $cart_rule) {
                            if ($tmp_cart_rule['id_cart_rule'] == $cart_rule['id_cart_rule']) {
                                $flag = true;
                            }
                        }
                        if (!$flag) {
                            $cart_rules[] = $tmp_cart_rule;
                        }
                    }
                }

                $id_address_delivery = 0;
                if (isset($products[0])) {
                    $id_address_delivery = (is_null($products) ? $this->id_address_delivery : $products[0]['id_address_delivery']);
                }
                $package = array('id_carrier' => $id_carrier, 'id_address' => $id_address_delivery, 'products' => $products);

                // Then, calculate the contextual value for each one
                $flag = false;
                foreach ($cart_rules as $cart_rule) {
                    // If the cart rule offers free shipping, add the shipping cost
                    if (($with_shipping || $type == Cart::ONLY_DISCOUNTS) && $cart_rule['obj']->free_shipping && !$flag) {
                        $order_shipping_discount = (float) Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), $compute_precision);
                        $flag = true;
                    }

                    // If the cart rule is a free gift, then add the free gift value only if the gift is in this package
                    if ((int) $cart_rule['obj']->gift_product) {
                        $in_order = false;
                        if (is_null($products)) {
                            $in_order = true;
                        } else {
                            foreach ($products as $product) {
                                if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute']) {
                                    $in_order = true;
                                }
                            }
                        }

                        if ($in_order) {
                            $order_total_discount += $cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
                        }
                    }

                    // If the cart rule offers a reduction, the amount is prorated (with the products in the package)
                    if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0) {
                        $order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), $compute_precision);
                    }
                }
                $order_total_discount = min(Tools::ps_round($order_total_discount, 2), (float) $order_total_products) + (float) $order_shipping_discount;
                $order_total -= $order_total_discount;
            }

            if ($type == Cart::BOTH) {
                $order_total += $shipping_fees + $wrapping_fees;
            }

            if ($order_total < 0 && $type != Cart::ONLY_DISCOUNTS) {
                return 0;
            }

            if ($type == Cart::ONLY_DISCOUNTS) {
                return $order_total_discount;
            }

            return Tools::ps_round((float) $order_total, $compute_precision);
        } else {
            if (!$this->id)
			return 0;

            $type = (int)$type;
            $array_type = array(
                    Cart::ONLY_PRODUCTS,
                    Cart::ONLY_DISCOUNTS,
                    Cart::BOTH,
                    Cart::BOTH_WITHOUT_SHIPPING,
                    Cart::ONLY_SHIPPING,
                    Cart::ONLY_WRAPPING,
                    Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING,
                    Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
            );

            // Define virtual context to prevent case where the cart is not the in the global context
            $virtual_context = Context::getContext()->cloneContext();
            $virtual_context->cart = $this;

            if (!in_array($type, $array_type))
                    die(Tools::displayError());

            $with_shipping = in_array($type, array(Cart::BOTH, Cart::ONLY_SHIPPING));

            // if cart rules are not used
            if ($type == Cart::ONLY_DISCOUNTS && !CartRule::isFeatureActive())
                    return 0;

            // no shipping cost if is a cart with only virtuals products
            $virtual = $this->isVirtualCart();
            if ($virtual && $type == Cart::ONLY_SHIPPING)
                    return 0;

            if ($virtual && $type == Cart::BOTH)
                    $type = Cart::BOTH_WITHOUT_SHIPPING;

            if ($with_shipping)
            {
                if (is_null($products) && is_null($id_carrier)){
                    if (Module::isInstalled('oyst') || Module::isEnabled('oyst')) {
                        if ($payment_method != null && preg_match('/OneClick/i', $payment_method)) {
                            $shipping_fees = $this->getTotalShippingCostOyst(null, (boolean)$with_taxes, null, $payment_method);
                        } else {
                            $shipping_fees = $this->getTotalShippingCost(null, (boolean)$with_taxes);
                        }
                    } else {
                        $shipping_fees = $this->getTotalShippingCost(null, (boolean)$with_taxes);
                    }
                }
                else {
                    if (Module::isInstalled('oyst') || Module::isEnabled('oyst')) {
                        if ($payment_method != null && preg_match('/OneClick/i', $payment_method)) {
                            $shipping_fees = $this->getPackageShippingCostOyst($id_carrier, (int)$with_taxes, null, $products);
                        } else {
                            $shipping_fees = $this->getPackageShippingCost($id_carrier, (int)$with_taxes, null, $products);
                        }
                    } else {
                        $shipping_fees = $this->getPackageShippingCost($id_carrier, (int)$with_taxes, null, $products);
                    }
                }
            }
            else {
                    $shipping_fees = 0;
            }
            
            if ($type == Cart::ONLY_SHIPPING)
                    return $shipping_fees;

            if ($type == Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)
                    $type = Cart::ONLY_PRODUCTS;

            $param_product = true;
            if (is_null($products))
            {
                    $param_product = false;
                    $products = $this->getProducts();
            }

            if ($type == Cart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING)
            {
                    foreach ($products as $key => $product)
                            if ($product['is_virtual'])
                                    unset($products[$key]);
                    $type = Cart::ONLY_PRODUCTS;
            }

            $order_total = 0;
            if (Tax::excludeTaxeOption())
                    $with_taxes = false;

            foreach ($products as $product) // products refer to the cart details
            {
                    if ($virtual_context->shop->id != $product['id_shop'])
                            $virtual_context->shop = new Shop((int)$product['id_shop']);

                    if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice')
                            $address_id = (int)$this->id_address_invoice;
                    else
                            $address_id = (int)$product['id_address_delivery']; // Get delivery address of the product from the cart
                    if (!Address::addressExists($address_id))
                            $address_id = null;

                    if ($this->_taxCalculationMethod == PS_TAX_EXC)
                    {
                            // Here taxes are computed only once the quantity has been applied to the product price
                            $price = Product::getPriceStatic(
                                    (int)$product['id_product'],
                                    false,
                                    (int)$product['id_product_attribute'],
                                    2,
                                    null,
                                    false,
                                    true,
                                    $product['cart_quantity'],
                                    false,
                                    (int)$this->id_customer ? (int)$this->id_customer : null,
                                    (int)$this->id,
                                    $address_id,
                                    $null,
                                    true,
                                    true,
                                    $virtual_context
                            );

                            $total_ecotax = $product['ecotax'] * (int)$product['cart_quantity'];
                            $total_price = $price * (int)$product['cart_quantity'];

                            if ($with_taxes)
                            {
                                    $product_tax_rate = (float)Tax::getProductTaxRate((int)$product['id_product'], (int)$address_id, $virtual_context);
                                    $product_eco_tax_rate = Tax::getProductEcotaxRate((int)$address_id);

                                    $total_price = ($total_price - $total_ecotax) * (1 + $product_tax_rate / 100);
                                    $total_ecotax = $total_ecotax * (1 + $product_eco_tax_rate / 100);
                                    $total_price = Tools::ps_round($total_price + $total_ecotax, 2);
                            }
                    }
                    else
                    {
                            if ($with_taxes)
                                    $price = Product::getPriceStatic(
                                            (int)$product['id_product'],
                                            true,
                                            (int)$product['id_product_attribute'],
                                            2,
                                            null,
                                            false,
                                            true,
                                            $product['cart_quantity'],
                                            false,
                                            ((int)$this->id_customer ? (int)$this->id_customer : null),
                                            (int)$this->id,
                                            ((int)$address_id ? (int)$address_id : null),
                                            $null,
                                            true,
                                            true,
                                            $virtual_context
                                    );
                            else
                                    $price = Product::getPriceStatic(
                                            (int)$product['id_product'],
                                            false,
                                            (int)$product['id_product_attribute'],
                                            2,
                                            null,
                                            false,
                                            true,
                                            $product['cart_quantity'],
                                            false,
                                            ((int)$this->id_customer ? (int)$this->id_customer : null),
                                            (int)$this->id,
                                            ((int)$address_id ? (int)$address_id : null),
                                            $null,
                                            true,
                                            true,
                                            $virtual_context
                                    );

                            $total_price = Tools::ps_round($price * (int)$product['cart_quantity'], 2);
                    }
                    $order_total += $total_price;
            }

            $order_total_products = $order_total;

            if ($type == Cart::ONLY_DISCOUNTS)
                    $order_total = 0;

            // Wrapping Fees
            $wrapping_fees = 0;
            if ($this->gift)
                    $wrapping_fees = Tools::convertPrice(Tools::ps_round($this->getGiftWrappingPrice($with_taxes), 2), Currency::getCurrencyInstance((int)$this->id_currency));
            if ($type == Cart::ONLY_WRAPPING)
                    return $wrapping_fees;

            $order_total_discount = 0;
            if (!in_array($type, array(Cart::ONLY_SHIPPING, Cart::ONLY_PRODUCTS)) && CartRule::isFeatureActive())
            {
                    // First, retrieve the cart rules associated to this "getOrderTotal"
                    if ($with_shipping || $type == Cart::ONLY_DISCOUNTS)
                            $cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_ALL);
                    else
                    {
                            $cart_rules = $this->getCartRules(CartRule::FILTER_ACTION_REDUCTION);
                            // Cart Rules array are merged manually in order to avoid doubles
                            foreach ($this->getCartRules(CartRule::FILTER_ACTION_GIFT) as $tmp_cart_rule)
                            {
                                    $flag = false;
                                    foreach ($cart_rules as $cart_rule)
                                            if ($tmp_cart_rule['id_cart_rule'] == $cart_rule['id_cart_rule'])
                                                    $flag = true;
                                    if (!$flag)
                                            $cart_rules[] = $tmp_cart_rule;
                            }
                    }

                    $id_address_delivery = 0;
                    if (isset($products[0]))
                            $id_address_delivery = (is_null($products) ? $this->id_address_delivery : $products[0]['id_address_delivery']);
                    $package = array('id_carrier' => $id_carrier, 'id_address' => $id_address_delivery, 'products' => $products);

                    // Then, calculate the contextual value for each one
                    foreach ($cart_rules as $cart_rule)
                    {
                            // If the cart rule offers free shipping, add the shipping cost
                            if (($with_shipping || $type == Cart::ONLY_DISCOUNTS) && $cart_rule['obj']->free_shipping)
                                    $order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), 2);

                            // If the cart rule is a free gift, then add the free gift value only if the gift is in this package
                            if ((int)$cart_rule['obj']->gift_product)
                            {
                                    $in_order = false;
                                    if (is_null($products))
                                            $in_order = true;
                                    else
                                            foreach ($products as $product)
                                                    if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute'])
                                                            $in_order = true;

                                    if ($in_order)
                                            $order_total_discount += $cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_GIFT, $package, $use_cache);
                            }

                            // If the cart rule offers a reduction, the amount is prorated (with the products in the package)
                            if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0)
                                    $order_total_discount += Tools::ps_round($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), 2);
                    }
                    $order_total_discount = min(Tools::ps_round($order_total_discount, 2), $wrapping_fees + $order_total_products + $shipping_fees);
                    $order_total -= $order_total_discount;
            }

            if ($type == Cart::BOTH)
                    $order_total += $shipping_fees + $wrapping_fees;

            if ($order_total < 0 && $type != Cart::ONLY_DISCOUNTS)
                    return 0;

            if ($type == Cart::ONLY_DISCOUNTS)
                    return $order_total_discount;

            return Tools::ps_round((float)$order_total, 2);
        }
    }

}
