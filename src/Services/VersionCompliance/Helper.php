<?php

namespace Oyst\Services\VersionCompliance;

class Helper {

    public function getCartProductsWithSeparatedGifts($cart) {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $products = $cart->getProducts();
            //Check if they have gift product on the list
            $cart_rules = $cart->getCartRules();

            foreach ($products as &$product) {
                $product['is_gift'] = 0;
            }
            foreach ($cart_rules as $cart_rule) {
                if ($cart_rule['gift_product']) {
                    foreach ($products as $key => &$product) {
                        if ($product['id_product'] == $cart_rule['gift_product'] && $product['id_product_attribute'] == $cart_rule['gift_product_attribute']) {
                            //If product has a quantity grater than 1, we need to duplicate the product has a gift and decrease his quantity
                            if ($product['quantity'] > 1) {
                                $gift_product = $product;
                                $gift_product['is_gift'] = 1;
                                $gift_product['cart_quantity'] = 1;
                                $gift_product['quantity'] = 1;
                                $products[] = $gift_product;
                                $product['quantity'] -= 1;
                                $product['cart_quantity'] -= 1;
                            } else {
                                $product['is_gift'] = 1;
                            }
                            break; // One gift product per cart rule
                        }
                    }
                }
            }
            return $products;
        } else {
            return $cart->getProductsWithSeparatedGifts();
        }
    }
}
