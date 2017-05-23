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
}
