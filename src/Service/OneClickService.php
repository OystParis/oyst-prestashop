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
use Cart;
use Oyst;
use Group;
use Tools;
use Module;
use Address;
use Context;
use Country;
use Product;
use CartRule;
use Currency;
use Customer;
use Validate;
use Exception;
use Combination;
use StockAvailable;
use Oyst\Classes\OystUser;
use Oyst\Classes\OystPrice;
use Oyst\Classes\OystAddress;
use Oyst\Classes\OystProduct;
use Configuration as ConfigurationP;
use Oyst\Classes\OneClickOrderParams;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Classes\OneClickCustomization;
use Oyst\Classes\OneClickNotifications;
use Oyst\Factory\AbstractExportProductServiceFactory;

/**
 * Class Oyst\Service\OneClickService
 */
class OneClickService extends AbstractOystService
{
    const ONE_CLICK_VERSION = 2;

    /**
     * @param Product $product
     * @param $quantity
     * @param Combination|null $combination
     * @param OystUser|null $user
     * @return array
     * @throws Exception
     */
    public function authorizeNewOrder(
        $productLess = null,
        OneClickNotifications $notifications = null,
        OystUser $user = null,
        OneClickOrderParams $orderParams = null,
        $context = null,
        OneClickCustomization $customization = null
    ) {
        $products = array();

        if (!is_array($productLess)) {
            $products[] = $productLess;
        } else {
            $products = $productLess;
        }
        $response = $this->requester->call('authorizeOrderV2', array(
            $products,
            $notifications,
            $user,
            $orderParams,
            $context,
            $customization
        ));

        $apiClient = $this->requester->getApiClient();
        if ($apiClient->getLastHttpCode() == 200) {
            $result = array(
                'url' => $response['url'],
                'state' => true,
            );
        } else {
            $result = array(
                'error' => $apiClient->getLastError(),
                'state' => false,
            );
        }

        return $result;
    }

    /**
     * @param CurrentRequest $request
     * @return array
     */
    public function requestAuthorizeNewOrderProcess(CurrentRequest $request)
    {

        $data = array(
            'state' => false,
        );

        $product = null;
        $combination = null;
        $quantity = 0;
        $products = null;
        $controller = $request->getRequestItem('controller');
        $products_less = array();
        $result_products = array();
        $oyst = new Oyst();
        // Deprecated ??
        Context::getContext()->currency = new Currency(ConfigurationP::get('PS_CURRENCY_DEFAULT'));
        $exportProductService = AbstractExportProductServiceFactory::get($oyst, Context::getContext());
        $this->context->currency = new Currency(Currency::getIdByIsoCode('EUR'));
        $customer = $this->context->customer;

        // if ($request->hasRequest('labelCta')) {
            $labelCta = $request->getRequestItem('labelCta');
        // } else {
        //     $labelCta = false;
        // }

        // Get usetax for group
        $usetax = true;

        if ($customer) {
            if (Group::getPriceDisplayMethod($customer->id_default_group) == 1) {
                $usetax = false;
            }
        }

        if ($oyst->displayBtnCart($controller)) {
            // Check validity cart rule ?
            if (version_compare(_PS_VERSION_, '1.6.0', '>=')) {
                $ids_cart_rule_gift = Context::getContext()->cart->getCartRules(CartRule::FILTER_ACTION_GIFT);

                foreach ($ids_cart_rule_gift as $cr) {
                    $cart_rule = new CartRule($cr['obj']->id, Context::getContext()->language->id);
                    Context::getContext()->cart->removeCartRule($cart_rule->id);
                    Context::getContext()->cart->update();
                }

                $products = Context::getContext()->cart->getProducts();
                // Apply cart rule for gift
                foreach ($ids_cart_rule_gift as $cr) {
                    $cart_rule = new CartRule($cr['obj']->id, Context::getContext()->language->id);
                    Context::getContext()->cart->addCartRule($cart_rule->id);
                    Context::getContext()->cart->update();
                }
            } else {
                $cart_clone = new Cart(Context::getContext()->cart->id);
                $ids_cart_rule_gift = Context::getContext()->cart->getCartRules(CartRule::FILTER_ACTION_GIFT);

                foreach ($ids_cart_rule_gift as $cr) {
                    $cart_rule = new CartRule($cr['obj']->id, Context::getContext()->language->id);
                    $cart_clone->removeCartRule($cart_rule->id);
                }

                $products = $cart_clone->getProducts();
                // Apply cart rule for gift
                foreach ($ids_cart_rule_gift as $cr) {
                    $cart_rule = new CartRule($cr['obj']->id, Context::getContext()->language->id);
                    $cart_clone->addCartRule($cart_rule->id);
                }
            }

            $ids_gift_products = array();
            if (Module::isInstalled('giftonordermodule') && Module::isEnabled('giftonordermodule')) {
                $sql = 'SELECT go.*
                        FROM `'._DB_PREFIX_.'giftonorder_order` as go
                        WHERE go.id_cart = '.(int)Context::getContext()->cart->id;

                $giftInCart = Db::getInstance()->ExecuteS($sql);
                if (!$giftInCart) {
                    $giftInCart = array();
                }
                if (count($giftInCart) > 0) {
                    foreach ($giftInCart as $gift) {
                        $ids_gift_products[] = $gift['id_product'];
                    }
                }
            }

            if (!$products && ($controller == 'order' || $controller == 'order-opc' )) {
                $data['error'] = 'Missing products';
            }
        } else {
            if (!Context::getContext()->cart->id) {
                $this->getCart();
            }

            $idProduct = (int)$request->getRequestItem('productId');
            $idCombination = (int)$request->getRequestItem('productAttributeId');
            $quantity = (int)$request->getRequestItem('quantity');

            Context::getContext()->cart->updateQty($quantity, (int)$idProduct, (int)$idCombination, false, 'up');

            $products = Context::getContext()->cart->getProducts();

            if (!$request->hasRequest('productId')) {
                $data['error'] = 'Missing product';
            } elseif (!$request->hasRequest('productAttributeId')) {
                $data['error'] = 'Missing combination, even none selected';
            } elseif (!$request->hasRequest('quantity')) {
                $data['error'] = 'Missing quantity';
            }
        }

        if (!$request->hasRequest('oneClick')) {
            $data['error'] = 'Missing parameters';
        }

        if (!isset($data['error'])) {
            if (!$products && ($controller == 'index' || $controller == 'category')) {
                $oystPrice = new OystPrice(10, $this->context->currency->iso_code);
                $oystProduct = new OystProduct('#OYST#', 'Product fictif', $oystPrice, 1);
                $oystProduct->__set('materialized', true);
                $products_less[] = $oystProduct;

                if (!$this->context->cookie->id_cart) {
                    $cart = new Cart();
                    $cart->id_lang = (int)$this->context->cookie->id_lang;
                    $cart->id_currency = (int)$this->context->cookie->id_currency;
                    $cart->id_guest = (int)$this->context->cookie->id_guest;
                    $cart->id_shop_group = (int)$this->context->shop->id_shop_group;
                    $cart->id_shop = $this->context->shop->id;
                    if ($this->context->cookie->id_customer) {
                        $cart->id_customer = (int)$this->context->cookie->id_customer;
                        $cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                        $cart->id_address_invoice = (int)$cart->id_address_delivery;
                    } else {
                        $cart->id_address_delivery = 0;
                        $cart->id_address_invoice = 0;
                    }
                    $cart->save();

                    // Needed if the merchant want to give a free product to every visitors
                    $this->context->cart = $cart;
                    $this->context->cookie->id_cart = $cart->id;
                }
            } elseif ($products) {
                $gift_products = $this->context->cart->getSummaryDetails()['gift_products'];
                $gifts = array();

                if ($gift_products) {
                    foreach ($gift_products as $gift) {
                        $gifts[$gift['id_product']] = $gift['id_product_attribute'];
                    }
                }

                foreach ($products as $product) {
                    if (Module::isInstalled('giftonordermodule') && Module::isEnabled('giftonordermodule')) {
                        if (count($ids_gift_products) > 0 && in_array($product['id_product'], $ids_gift_products)) {
                            continue;
                        }
                    }

                    if (count($gifts) > 0 &&
                        isset($gifts[$product['id_product']]) &&
                        $gifts[$product['id_product']] === $product['id_product_attribute']) {
                        continue;
                    }

                    $product_less = $exportProductService->transformProductLess(
                        $product['id_product'],
                        $product['id_product_attribute'],
                        $product['cart_quantity'],
                        $usetax
                    );

                    $customizations = Context::getContext()->cart->getProductCustomization($product['id_product']);

                    $product_less->__set('customizations', $this->prepareCustomizations($customizations));
                    $products_less[] = $product_less;
                }
            } else {
                $product = new Product($idProduct);
                if (!Validate::isLoadedObject($product)) {
                    $data['error'] = 'Product can\'t be found';
                }

                if ($request->hasRequest('productAttributeId')) {
                    if ($idCombination > 0) {
                        $combination = new Combination($idCombination);
                        if (!Validate::isLoadedObject($combination)) {
                            $data['error'] = 'Combination could not be found';
                        }
                    }
                }

                $quantity = (int)$request->getRequestItem('quantity');
                if ($quantity <= 0) {
                    $data['error'] = 'Bad quantity';
                }

                $product_less = $exportProductService->transformProductLess(
                    $idProduct,
                    $idCombination,
                    $quantity,
                    $usetax
                );

                $customizations = Context::getContext()->cart->getProductCustomization($idProduct);
                foreach ($customizations as &$customization) {
                    $customization['quantity'] = $quantity;
                }

                $product_less->__set('customizations', $this->prepareCustomizations($customizations));
                $products_less[] = $product_less;
            }
        }

        if (isset($data['error'])) {
            $this->logger->critical(sprintf('Error occurred during oneClick process: %s', $data['error']));
        }

        // Generated context
        $oystContext = array(
            'id' => (string)$this->generatedId(),
            'store_id' => (int)Context::getContext()->shop->id
        );

        //Get cart_rules ids
        $cart_rules = Context::getContext()->cart->getCartRules();
        if (!empty($cart_rules)) {
            foreach ($cart_rules as $cart_rule) {
                $oystContext['ids_cart_rule'][] = $cart_rule['obj']->id;
            }
        }

        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";

        if ($user_agent) {
            $oystContext['user_agent'] = $user_agent;
        }

        if ($this->context->cart->id) {
            $oystContext['id_cart'] = (int)$this->context->cart->id;
        }

        if (!isset($data['error'])) {
            $oystUser = null;
            if ($customer->isLogged()) {
                $oystUser = (new OystUser())
                    ->setFirstName($customer->firstname)
                    ->setLastName($customer->lastname)
                    ->setLanguage($this->context->language->iso_code)
                    ->setEmail($customer->email);
                $oystContext['id_user'] = $customer->id;

                $customerPS = new Customer($customer->id);
                // Get address for customer
                $addresses = $customerPS->getAddresses($this->context->language->id);
                // Get last address for array
                $last_address = end($addresses);
                $id_country = Country::getIdByName($this->context->language->id, $last_address['country']);
                $country_iso = Country::getIsoById($id_country);

                // Build OystAddress
                $oystAddress = new OystAddress();
                $oystAddress->setFirstName($customer->firstname);
                $oystAddress->setLastName($customer->lastname);
                if ($last_address['company'] != '') {
                    $oystAddress->setCompanyName($last_address['company']);
                }
                $oystAddress->setLabel($last_address['alias']);
                $oystAddress->setStreet($last_address['address1']);
                $oystAddress->setComplementary($last_address['address2']);
                $oystAddress->setCity($last_address['city']);
                $oystAddress->setPostCode($last_address['postcode']);
                if ($last_address['state'] != null) {
                    $oystAddress->setRegion($last_address['state']);
                }
                $oystAddress->setCountry($country_iso);

                $oystUser->addAddress($oystAddress);
            } else {
                $address_fake = new Address();
                $address_fake->firstname = 'FAKE';
                $address_fake->lastname = 'Customer';
                $address_fake->address1 = '98 rue de la victoire';
                $address_fake->postcode = '75000';
                $address_fake->city = 'Paris';
                $address_fake->alias = 'OystAddress';
                $address_fake->id_country = Country::getByIso('FR');
                $address_fake->phone = '0600000000';
                $address_fake->phone_mobile = '0600000000';

                $address_fake->add();

                $oystContext['id_address'] = $address_fake->id;
            }

            $oneClickOrdersParams = new OneClickOrderParams();
            foreach ($products_less as $product) {
                if (!$product->materialized) {
                    $oneClickOrdersParams->setIsMaterialized($product->materialized);
                } else {
                    $oneClickOrdersParams->setIsMaterialized(true);
                }
                $result_products[] = $product->toArray();
            }

            $delay = (int)ConfigurationP::get('FC_OYST_DELAY');

            if (is_numeric($delay) && $delay > 0) {
                $oneClickOrdersParams->setDelay($delay);
            } else {
                $oneClickOrdersParams->setDelay(15);
            }

            $oneClickOrdersParams->setShouldReinitBuffer(false);
            $oneClickOrdersParams->setIsCheckoutCart(true);
            $oneClickOrdersParams->setManageQuantity(ConfigurationP::get('FC_OYST_MANAGE_QUANTITY_CART'));

            $allowDiscountCoupon = (bool)ConfigurationP::get('FC_OYST_ALLOW_COUPON');
            if ($allowDiscountCoupon) {
                $oneClickOrdersParams->setAllowDiscountCoupon($allowDiscountCoupon);
            }

            $this->logger->info(
                sprintf(
                    'New notification oneClickOrdersParams [%s]',
                    Tools::jsonEncode($oneClickOrdersParams->toArray())
                )
            );

            $url = Context::getContext()->link->getModuleLink('oyst', 'oneclickreturn');
            $this->context->cookie->oyst_key = ConfigurationP::get('FC_OYST_HASH_KEY');
            $this->context->cookie->oyst_id_cart = Context::getContext()->cart->id;

            $oneClickCustomization = new OneClickCustomization();
            $oneClickCustomization->setCta($labelCta, $url);

            $oneClickNotifications = new OneClickNotifications();
            $oneClickNotifications->setShouldAskShipments(true);
            // Deprecated for v1.19
            $oneClickNotifications->setShouldAskStock(false);
            $oneClickNotifications->addEvent('order.cart.estimate');
            $oneClickNotifications->setUrl($this->oyst->getNotifyUrl());

            if ($oneClickCustomization) {
                $this->logger->info(
                    sprintf(
                        'New notification oneClickCustomization  [%s]',
                        Tools::jsonEncode($oneClickCustomization ->toArray())
                    )
                );
            }

            $this->logger->info(
                sprintf(
                    'New notification oneClickNotifications [%s]',
                    Tools::jsonEncode($oneClickNotifications->toArray())
                )
            );

            $this->logger->info(
                sprintf(
                    'New notification products [%s]',
                    Tools::jsonEncode($result_products)
                )
            );

            $this->logger->info(
                sprintf(
                    'New notification context [%s]',
                    Tools::jsonEncode($oystContext)
                )
            );

            $result = $this->authorizeNewOrder(
                $products_less,
                $oneClickNotifications,
                $oystUser,
                $oneClickOrdersParams,
                $oystContext,
                $oneClickCustomization
            );
            $data = array_merge($data, $result);
        }

        return $data;
    }

    public function generatedId()
    {
        $hash = ConfigurationP::get('FC_OYST_HASH_KEY');
        $datetime = new \DateTime();
        $datetime = $datetime->format('YmdHis');
        return uniqid().$hash.'-'.$datetime;
    }

    /**
     * @param $filename string
     */
    private function saveCustomizationFile($filename)
    {
        $oyst_dir = _PS_UPLOAD_DIR_.'oyst/';
        if (!file_exists($oyst_dir)) {
            mkdir($oyst_dir);
        }
        if (file_exists(_PS_UPLOAD_DIR_.$filename)) {
            copy(_PS_UPLOAD_DIR_.$filename, $oyst_dir.$filename);
            copy(_PS_UPLOAD_DIR_.$filename.'_small', $oyst_dir.$filename.'_small');
        }
    }

    /**
     * @param $customizations array Prestashop customizations
     * @return array sorted customizations
     */
    private function prepareCustomizations($customizations)
    {
        $sorted_customizations = array();
        //Save uploaded file in oyst folder for order creation
        foreach ($customizations as $customization) {
            if ((int)$customization['type'] == 0) {
                $this->saveCustomizationFile($customization['value']);
            }
            if (!isset($sorted_customizations[$customization['id_customization']])) {
                $sorted_customizations[$customization['id_customization']]['quantity'] = (int)$customization['quantity'];
            }
            $sorted_customizations[$customization['id_customization']]['data'][] = array(
                'type' => (int)$customization['type'],
                'index' => (int)$customization['index'],
                'value' => $customization['value'],
            );
        }
        return $sorted_customizations;
    }

    public function getCart()
    {
        if (!$this->context->cart->id) {
            $cart = new Cart();
            $cart->id_lang = (int)$this->context->cookie->id_lang;
            $cart->id_currency = (int)$this->context->cookie->id_currency;
            $cart->id_guest = (int)$this->context->cookie->id_guest;
            $cart->id_shop_group = (int)$this->context->shop->id_shop_group;
            $cart->id_shop = $this->context->shop->id;
            if ($this->context->cookie->id_customer) {
                $cart->id_customer = (int)$this->context->cookie->id_customer;
                $cart->id_address_delivery = (int)Address::getFirstCustomerAddressId($cart->id_customer);
                $cart->id_address_invoice = (int)$cart->id_address_delivery;
            } else {
                $cart->id_address_delivery = 0;
                $cart->id_address_invoice = 0;
            }
            $cart->save();

            // Needed if the merchant want to give a free product to every visitors
            $this->context->cart = $cart;
            $this->context->cookie->id_cart = $cart->id;
        }

        return true;
    }
}
