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

namespace Oyst\Classes;

class Configuration
{
    const ONE_CLICK_FEATURE_STATE = 'OYST_ONE_CLICK_FEATURE_STATE';
    const ONE_CLICK_FEATURE_ENABLE = 1;
    const ONE_CLICK_FEATURE_DISABLE = 0;
    const ONE_CLICK_CARRIER = 'OYST_ONE_CLICK_CARRIER';
    const ONE_CLICK_URL_PROD = 'OYST_ONECLICK_URL_PROD';
    const ONE_CLICK_URL_SANDBOX = 'OYST_ONECLICK_URL_SANDBOX';
    const ONE_CLICK_URL_CUSTOM = 'OYST_ONECLICK_URL_CUSTOM';

    const API_ENV_FREEPAY = 'OYST_API_ENV_FREEPAY';
    const API_ENV_ONECLICK = 'OYST_API_ENV_ONECLICK';

    const API_ENV_PROD = 'prod';
    const API_ENV_SANDBOX = 'sandbox';
    const API_ENV_CUSTOM = 'custom';

    const API_KEY_PROD_FREEPAY = 'OYST_API_PROD_KEY_FREEPAY';
    const API_KEY_SANDBOX_FREEPAY = 'OYST_API_SANDBOX_KEY_FREEPAY';
    const API_KEY_CUSTOM_FREEPAY = 'OYST_API_CUSTOM_KEY_FREEPAY';

    const API_KEY_PROD_ONECLICK = 'OYST_API_PROD_KEY_ONECLICK';
    const API_KEY_SANDBOX_ONECLICK = 'OYST_API_SANDBOX_KEY_ONECLICK';
    const API_KEY_CUSTOM_ONECLICK = 'OYST_API_CUSTOM_KEY_ONECLICK';

    const API_ENDPOINT_PROD_FREEPAY = 'OYST_API_PROD_ENDPOINT_FREEPAY';
    const API_ENDPOINT_SANDBOX_FREEPAY = 'OYST_API_SANDBOX_ENDPOINT_FREEPAY';
    const API_ENDPOINT_CUSTOM_FREEPAY = 'OYST_API_CUSTOM_ENDPOINT_FREEPAY';


    const API_ENDPOINT_PROD_ONECLICK = 'OYST_API_PROD_ENDPOINT_ONECLCK';
    const API_ENDPOINT_SANDBOX_ONECLICK = 'OYST_API_SANDBOX_ENDPOINT_ONECLCK';
    const API_ENDPOINT_CUSTOM_ONECLICK = 'OYST_API_CUSTOM_ENDPOINT_ONECLCK';

    // const CATALOG_EXPORT_STATE = 'OYST_CATALOG_EXPORT_STATE';
    // const CATALOG_EXPORT_RUNNING = 1;
    // const CATALOG_EXPORT_DONE = 0;

    const DISPLAY_ADMIN_INFO_STATE = 'OYST_DISPLAY_ADMIN_INFO_STATE';
    // const DISPLAY_ADMIN_INFO_ENABLE = 1;
    // const DISPLAY_ADMIN_INFO_DISABLE = 0;

    // const REQUESTED_CATALOG_DATE = 'OYST_REQUESTED_CATALOG_DATE';
}
