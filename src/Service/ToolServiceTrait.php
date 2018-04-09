<?php

namespace Oyst\Service;

use Customer;
use Tools;
use Configuration as PSConfiguration;
use Mail;

trait ToolServiceTrait
{
    /**
     * @param $user
     * @return Customer
     */
    public function getCustomer($user)
    {
        $customerInfo = Customer::getCustomersByEmail($user['email']);
        if (count($customerInfo)) {
            $customer = new Customer($customerInfo[0]['id_customer']);
        } else {
            $firstname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $user['first_name']);
            if (isset(Customer::$definition['fields']['firstname']['size'])) {
                $firstname = Tools::substr($firstname, 0, Customer::$definition['fields']['firstname']['size']);
            }

            $lastname = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $user['last_name']);
            if (isset(Customer::$definition['fields']['lastname']['size'])) {
                $lastname = Tools::substr($lastname, 0, Customer::$definition['fields']['lastname']['size']);
            }

            $customer = new Customer();
            $customer->email = $user['email'];
            $customer->firstname = $firstname;
            $customer->lastname = $lastname;
            if (version_compare(_PS_VERSION_, '1.5.4.0', '>=')) {
                $customer->id_lang = PSConfiguration::get('PS_LANG_DEFAULT');
            }
            $password = Tools::passwdGen();
            $customer->passwd = Tools::encrypt($password);
            $customer->add();

            $this->sendConfirmationMail($customer, $password);
        }

        return $customer;
    }

    /**
     * sendConfirmationMail
     * @param Customer $customer
     * @return bool
     */
    private function sendConfirmationMail(Customer $customer, $password = '')
    {
        if (!PSConfiguration::get('PS_CUSTOMER_CREATION_EMAIL')) {
            return true;
        }

        if (empty($password)) {
            $password = str_repeat('*', strlen(Tools::getValue('passwd')));
        }

        return Mail::Send(
            $this->context->language->id,
            'account',
            Mail::l('Welcome!'),
            array(
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{passwd}' => $password,
            ),
            $customer->email,
            $customer->firstname.' '.$customer->lastname
        );
    }
}
