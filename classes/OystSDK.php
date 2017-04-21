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

class OystSDK
{
    private $_api_key;
    private $_api_endpoint;

    public function testCatalogRequest()
    {
        // Get products
        $oyst_product = new OystProduct();
        $return = $oyst_product->sendCatalog(0, 1);
        $return = Tools::jsonDecode($return, true);
        $result = (isset($return['statusCode'])  && $return['statusCode'] == 200) ? true : false;
        return array('result' => $result, 'values' => $return);
    }

    public function paymentRequest($amount, $currency, $id_cart, $urls, $is_3d, $user)
    {
        $data = array(
            'amount' => array(
                'value' => (float)$amount,
                'currency' => (string)$currency,
            ),
            'is_3d' => $is_3d,
            'notification_url' => $urls['notification'],
            'order_id' => (string)$id_cart,
            'redirects' => array(
                'cancel_url' => $urls['cancel'],
                'error_url' => $urls['error'],
                'return_url' => $urls['return'],
            ),
            'user' => $user,
        );
        return $this->_apiPostRequest($this->getApiEndpoint().'/payments', $data);
    }

    public function cancelOrRefundRequest($payment_id)
    {
        return $this->_apiPostRequest($this->getApiEndpoint().'/payments/'.$payment_id.'/cancel_or_refund', array());
    }

    public function refundRequest($payment_id, $value, $currency)
    {
        $data = array(
            'amount' => array(
                'value' => $value,
                'currency' => $currency
            )
        );

        return $this->_apiPostRequest($this->getApiEndpoint().'/payments/'.$payment_id.'/refund', $data);
    }

    public function productPostRequest($products)
    {
        $data = array('products' => $products);
        return $this->_apiPostRequest($this->getApiEndpoint().'/products', $data);
    }

    private function _apiPostRequest($endpoint, $data)
    {
        $data_string = Tools::jsonEncode($data);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.Tools::strlen($data_string),
            'User-Agent: OystPrestashop/'._PS_OYST_VERSION_.' (Prestashop '._PS_VERSION_.')',
            'Authorization: bearer '.$this->getApiKey(),
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 2000);

        return curl_exec($ch);
    }

    /**
     * @param string $name
     * @param string $phone
     * @param string $email
     * @param int    $nbTransac
     *
     * @return bool
     */
    public static function notify($name, $phone, $email, $nbTransac)
    {
        $psUrl  = 'https://partners-subscribe.prestashop.com/oyst/request.php';
        $params = array(
            'ps_version'   => _PS_VERSION_,
            'oyst_version' => _PS_OYST_VERSION_,
            'url'          => Tools::getHttpHost(true).__PS_BASE_URI__.' (~ '.$nbTransac.' transactions)',
            'name'         => $name,
            'phone'        => $phone,
            'email'        => $email,
            'channel'      => 'plugin-alerts'
        );
        $urlToCall = $psUrl.'?'.http_build_query($params);

        $ch = curl_init($urlToCall);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2000);

        $return   = curl_exec($ch);
        $response = Tools::jsonDecode($return, true);

        if (isset($response['status']) && $response['status'] == 'OK') {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->_api_key;
    }

    /**
     * @param mixed $api_key
     * @return OystLib
     */
    public function setApiKey($api_key)
    {
        $this->_api_key = $api_key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiEndpoint()
    {
        return $this->_api_endpoint;
    }

    /**
     * @param mixed $api_endpoint
     * @return OystLib
     */
    public function setApiEndpoint($api_endpoint)
    {
        $this->_api_endpoint = $api_endpoint;
        return $this;
    }
}
