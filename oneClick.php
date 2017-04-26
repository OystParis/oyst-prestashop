<?php

use Oyst\Controller\OneClickOrderController;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Service\Logger\PrestaShopLogger;

require_once __DIR__.'/../../config/config.inc.php';
require __DIR__.'/oyst.php';

$logger = new PrestaShopLogger();
$logger->info(sprintf('New OneClick request from customer: %d', Context::getContext()->customer->id));

$oneClickController = new OneClickOrderController(new CurrentRequest());
$oneClickController->setLogger($logger);
$oneClickController->authorizeOrderAction();
