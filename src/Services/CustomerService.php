<?php

namespace Oyst\Services;

use Customer;
use Db;
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

    /**
     * @param $id_customer
     * @return array
     */
    public function getIpsFromIdCustomer($id_customer)
    {
        $res = Db::getInstance()->executeS("SELECT INET_NTOA(`c`.`ip_address`) `ip_address` 
            FROM `"._DB_PREFIX_."connections` `c` 
            INNER JOIN `"._DB_PREFIX_."guest` `g` ON `c`.`id_guest` = `g`.`id_guest`
            WHERE `g`.`id_customer` = ".$id_customer);
        $ips = array();
        foreach ($res as $r) {
            $ips[] = $r['ip_address'];
        }
        return array_unique($ips);
    }

    /**
     * @param $id_customer
     * @return string
     */
    public function getLastIpFromIdCustomer($id_customer)
    {
        $ips = $this->getIpsFromIdCustomer($id_customer);
        return (isset($ips[0]) ? $ips[0] : '');
    }
}
