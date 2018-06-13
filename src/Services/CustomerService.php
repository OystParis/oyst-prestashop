<?php

namespace Oyst\Services;

use Customer;
use Language;
use Validate;

class CustomerService {

    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new CustomerService();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function __clone() {}

    public function searchCustomer($customer_infos)
    {
        $id_lang_fr = Language::getIdByIso('fr');

        //Search on id
        if (!empty($customer_infos['id_customer'])) {
            $customer = new Customer((int)$customer_infos['id_customer']);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        //Search on email
        if (empty($addresses) && !empty($customer_infos['email'])) {
            $customer = new Customer();
            $customer->getByEmail($customer_infos['email']);
            if (Validate::isLoadedObject($customer)) {
                $addresses = $customer->getAddresses($id_lang_fr);
            }
        }

        $results = array();

        if (Validate::isLoadedObject($customer)) {
            $results['customer_obj'] = $customer;
        }

        if (!empty($addresses)) {
            $results['addresses'] = $addresses;
        }
        return $results;
    }
}
