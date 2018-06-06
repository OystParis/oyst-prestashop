<?php

include dirname(__FILE__).'/../../config/config.inc.php';
include(dirname(__FILE__).'/../../init.php');

header('Content-Type: application/json');

if (!isset($_GET['action'])) {
    die(json_encode(array('error' => 'Action undefined')));
}

switch ($_GET['action']) {
    case 'get_id_cart':
        $id_cart = Context::getContext()->cart->id;
        die(json_encode(array('id_cart' => $id_cart)));
        break;
    default:
        die(json_encode(array('error' => 'Unknown action')));
}

