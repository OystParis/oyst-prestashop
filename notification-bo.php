<?php

require __DIR__.'/../../config/config.inc.php';
require __DIR__.'/oyst.php';

$response = [
    'state' => false,
];

switch (Tools::getValue('action')) {
    case 'hideExportInfo':
        $oyst = (new Oyst())->setAdminPanelInformationVisibility(false);
        $response['state'] = true;
        break;
}

echo json_encode($response);
