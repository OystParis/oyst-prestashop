<?php

use Configuration;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../vendor/autoload.php';

function upgrade_module_2_2_0()
{
    $fields_to_globalize = [
        'OYST_OS_PAY_WAITING_VALIDATION',
        'OYST_OS_PAYMENT_CAPTURED',
        'OYST_OS_PAY_WAITING_TO_CAPTURE',
        'OYST_OS_PAYMENT_TO_CAPTURE',
        'OYST_ORDER_STATUS_PARTIAL_REFUND',
        'OYST_OS_FRAUD_CHECK',
        'OYST_API_KEY',
    ];

    $res = true;

    foreach ($fields_to_globalize as $field_to_globalize) {
        $current_value = Configuration::get($field_to_globalize);
        $res &= Configuration::deleteByName($field_to_globalize);
        if (!empty($current_value)) {
            $res &= Configuration::updateGlobalValue($field_to_globalize, $current_value);
        }
    }
    return $res;
}
