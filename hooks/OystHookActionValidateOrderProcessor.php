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

/**
 * Class OystHookActionValidateOrderProcessor
 */
class OystHookActionValidateOrderProcessor extends FroggyHookProcessor
{
    /**
     * @return bool
     */
    public function run()
    {
        /** @var Order $order */
        $order = $this->params['order'];
        $amount = $order->getTotalPaid() * 100;
        // Get cURL resource
        // $ch = curl_init();

        // Set url
        // curl_setopt($ch, CURLOPT_URL, 'https://api.staging.oyst.eu/events/oneclick');
        // curl_setopt($ch, CURLOPT_URL, 'https://api.sandbox.oyst.eu/events/oneclick');
        // curl_setopt($ch, CURLOPT_URL, 'https://api.oyst.com/events/oneclick');

        // Set method
        // curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        // Set options
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Set headers
        // curl_setopt(
        //     $ch,
        //     CURLOPT_HTTPHEADER,
        //     ["Content-Type: application/json; charset=utf-8"]
        // );
        // Create body
        // $json_array = array(
        //     "referrer" => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "",
        //     "tag" => "merchantconfirmationpage:display:",
        //     "oyst_cookie" => "",
        //     "user_agent" => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "",
        //     "cart_amount" => $amount,
        //     "timestamp" => time()
        // );

        // $body = json_encode($json_array);

        // Set body
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        // Send the request & save response to $resp
        // $resp = curl_exec($ch);

        // if(!$resp) {
        //   die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        // } else {
        //   echo "Response HTTP Status Code : " . curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //   echo "\nResponse HTTP Body : " . $resp;
        // }

        // Close request to clear up some resources
        // curl_close($ch);
    }
}
