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

/*
 * Include Oyst SDK
 */
if (!class_exists('OystSDK', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystSDK.php';
}

class OystPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $currency = new Currency($this->context->cart->id_currency);
        $total_amount = (int)round($this->context->cart->getOrderTotal() * 100);

        // Build user variables
        $addresses_oyst = array();
        $addresses = array(
            new Address($this->context->cart->id_address_invoice),
            new Address($this->context->cart->id_address_delivery),
        );
        $main_phone = '';
        if (isset($addresses[0]->phone_mobile) && !empty($addresses[0]->phone_mobile)) {
            $main_phone = $addresses[0]->phone_mobile;
        }
        if (empty($main_phone) && isset($addresses[0]->phone) && !empty($addresses[0]->phone)) {
            $main_phone = $addresses[0]->phone;
        }

        foreach ($addresses as $ka => $address) {
            $country = new Country($address->id_country, $this->context->language->id);
            $addresses_oyst[] = array(
                'first_name' => $address->firstname,
                'last_name' => $address->lastname,
                'country' => $country->name,
                'city' => $address->city,
                'label' => $address->alias,
                'postcode' => $address->postcode,
                'street' => $address->address1,
            );
        }

        $customer_data = array();
        $customer_fields = array(
            'id_gender', 'id_lang', 'lastname', 'firstname', 'birthday', 'email', 'newsletter', 'newsletter_date_add',
            'optin', 'website', 'company', 'siret', 'ape', 'active', 'date_add', 'date_upd',
        );
        foreach ($customer_fields as $field) {
            $customer_data[$field] = $this->context->customer->$field;
        }

        $user = array(
            'additional_data' => array(
                'customer' => $customer_data,
                'addresses' => $this->context->customer->getAddresses($this->context->customer->id_lang),
            ),
            'addresses' => array($addresses_oyst[1]),
            'billing_addresses' => array($addresses_oyst[0]),
            'email' => $this->context->customer->email,
            'first_name' => $this->context->customer->firstname,
            'language' => $this->context->language->iso_code,
            'last_name' => $this->context->customer->lastname,
            'phone' => $main_phone,
        );

        $urls = $this->getUrls();

        // Make Oyst api call
        $oyst_api = new OystSDK();
        $oyst_api->setApiEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
        $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
        $result = $oyst_api->paymentRequest($total_amount, $currency->iso_code, $this->context->cart->id, $urls, false, $user);

        // Result payment
        $this->module->log($user);
        $this->module->logNotification('Result payment', $result);

        // Redirect to payment
        $result = Tools::jsonDecode($result, true);
        if (isset($result['url']) && !empty($result['url'])) {
            Tools::redirect($result['url']);
        }

        // Redirect to error page, save data in
        $function = 'base64'.'_'.'encode';
        $this->context->cookie->oyst_debug = $function(
            Tools::jsonEncode(
                array_merge(
                    $user,
                    $result,
                    array($total_amount, $currency->iso_code, $this->context->cart->id, $urls, true)
                )
            )
        );
        Tools::redirect($urls['error']);
    }

    /**
     * @return array
     */
    private function getUrls()
    {
        // Build hash
        $cart_hash = md5(Tools::jsonEncode(array($this->context->cart->id, $this->context->cart->nbProducts())));

        // Build urls and amount
        $glue = '&';
        if (Configuration::get('PS_REWRITING_SETTINGS') == 1) {
            $glue = '?';
        }

        $notification = $this->context->link->getModuleLink('oyst', 'paymentNotification').$glue.'key='.Configuration::get('FC_OYST_HASH_KEY').'&ch='.$cart_hash;
        $errorUrl     = $this->getUrlByName(Configuration::get('FC_OYST_REDIRECT_ERROR'), Configuration::get('FC_OYST_REDIRECT_ERROR_CUSTOM'));
        $successUrl   = $this->context->link->getModuleLink('oyst', 'paymentReturn').$glue.'id_cart='.$this->context->cart->id.'&key='.$this->context->customer->secure_key;

        $urls = array(
            'notification' => $notification,
            'cancel'       => $errorUrl,
            'error'        => $errorUrl,
            'return'       => $successUrl
        );

        return $urls;
    }

    private function getUrlByName($urlName, $customUrl)
    {
        $url = '';

        switch($urlName) {
            case 'ORDER_HISTORY':
                $url = $this->context->link->getPageLink('history');
                break;
            case 'PAYMENT_ERROR':
                $url = $this->context->link->getModuleLink('oyst', 'paymentError');
                break;
            case 'CART':
                $url = $this->context->link->getPageLink('order');
                break;
            case 'CUSTOM':
                $url = $customUrl;
                break;
        }

        return $url;
    }
}
