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
defined('_PS_VERSION_') || require dirname(__FILE__) . '/index.php';

// When sending the module to PrestaShop Validator, all the require files will be available
if (!file_exists(dirname(__FILE__).'/vendor/autoload.php')) {
    throw new Exception('Please install composer inside the oyst module');
}

require_once dirname(__FILE__).'/vendor/autoload.php';

define('_PS_OYST_DEBUG_', 0);
