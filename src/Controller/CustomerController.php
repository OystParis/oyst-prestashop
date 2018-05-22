<?php

namespace Oyst\Controller;

use Customer;
use Db;
use Language;
use Validate;

class CustomerController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('customer');
    }

    public function search($params)
    {
        $id_lang_fr = Language::getIdByIso('fr');

        //Search on id
        if (!empty($params['data']['id'])) {
            $customer = new Customer((int)$params['data']['id']);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        //Search on email
        if (empty($addresses) && !empty($params['data']['email'])) {
            $customer = new Customer();
            $customer->getByEmail($params['data']['email']);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        //Search on phone number
        if (empty($addresses) && !empty($params['data']['phone'])) {
            $id_customer = Db::getInstance()->getValue("SELECT `c`.`id_customer` 
                FROM `"._DB_PREFIX_."customer` `c`
                INNER JOIN `"._DB_PREFIX_."address` `a` ON `c`.`id_customer` = `a`.`id_customer`
                WHERE `a`.`phone` LIKE '".$params['data']['phone']."' OR `a`.`phone_mobile` LIKE '".$params['data']['phone']."'
                ORDER BY `c`.`date_upd` DESC");

            $customer = new Customer($id_customer);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        if (!empty($addresses)) {
            $this->respondAsJson($addresses);
        } else {
            $this->respondAsJson('No address found');
        }
    }
}
