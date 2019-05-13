<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/../vendor/autoload.php';

function upgrade_module_2_2_4()
{
    $oyst = new Oyst();
    return $oyst->registerHook('header');
}
