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

header('Access-Control-Allow-Origin: *');

use Oyst\Controller\OneClickOrderController;
use Oyst\Service\Http\CurrentRequest;

require_once dirname(__FILE__).'/../../config/config.inc.php';
require_once dirname(__FILE__).'/../../init.php';
require dirname(__FILE__).'/oyst.php';

$logger = new \Oyst\Service\Logger\FileLogger();
$logger->setFile(dirname(__FILE__).'/logs/oneClick.log');
$logger->info(sprintf('New OneClick request from customer: %d', Context::getContext()->customer->id));

$token = hash('sha256', Tools::jsonEncode(array(Configuration::get('FC_OYST_HASH_KEY'), _COOKIE_KEY_)));

if (Tools::getValue('key') != $token) {
    $logger->info('New notification : Secure key is invalid');
    die('Secure key is invalid');
}

$oneClickController = new OneClickOrderController(new CurrentRequest());
$oneClickController->setLogger($logger);
$oneClickController->authorizeOrderAction();
