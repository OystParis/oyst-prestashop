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

require dirname(__FILE__).'/../../config/config.inc.php';
require dirname(__FILE__).'/oyst.php';

if (Tools::getValue('key') != Configuration::get('FC_OYST_HASH_KEY')) {
    die('Secure key is invalid');
}

$response = array(
    'state' => false,
);

switch (Tools::getValue('action')) {
    case 'hideExportInfo':
        $oyst = (new Oyst())->setAdminPanelInformationVisibility(false);
        $response['state'] = true;
        break;

    case 'getNotificationsColumns':
        if (!Tools::getIsset('table'))
            break;

        $table = pSQL(Tools::getValue('table'));
        $results = Db::getInstance()->executeS("SHOW COLUMNS FROM ".$table."");
        $column_names = array();
        foreach ($results as $key => $result) {
            $column_names[] = $result['Field'];
        }
        $response['cols'] = $column_names;
        break;
    case 'getNotificationsData':

        if (!Tools::getIsset('table'))
            die(json_encode(array()));

        $table = pSQL(Tools::getValue('table'));
        $results = Db::getInstance()->executeS("SHOW COLUMNS FROM ".$table."");

        $columns = array();

        //Fallback if no primary key
        $primaryKey = $results[0]['Field'];
        foreach ($results as $key => $result) {
            if ($result['Key'] == 'PRI')
                $primaryKey = $result['Field'];

            // Array of database columns which should be read and sent back to DataTables.
            // The `db` parameter represents the column name in the database, while the `dt`
            // parameter represents the DataTables column identifier.
            $columns[] = array(
                'db' => $result['Field'],
                'dt' => $key
            );
        }

        $sql_details = array(
            'user' => _DB_USER_,
            'pass' => _DB_PASSWD_,
            'db'   => _DB_NAME_,
            'host' => _DB_SERVER_
        );

        require(_PS_MODULE_DIR_.'oyst/views/js/datatables/ssp.class.php');

        $response = SSP::simple($_GET, $sql_details, $table, $primaryKey, $columns);
        break;

}

echo json_encode($response);
