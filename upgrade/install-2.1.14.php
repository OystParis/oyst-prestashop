<?php

use Configuration as PSConfiguration;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../vendor/autoload.php';

function upgrade_module_2_1_14()
{
    $res = PSConfiguration::updateValue('OYST_ORDER_CREATION_STATUS', Configuration::get('OYST_OS_PAYMENT_CAPTURED'));

    $oyst_status_service = \Oyst\Services\OystStatusService::getInstance();
    $res &= $oyst_status_service->createStatus($oyst_status_service->status['oyst_fraud_check']);

    return $res;
}
