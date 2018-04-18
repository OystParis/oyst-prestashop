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

namespace Oyst\Service;

use Customer;
use Tools;
use Configuration as PSConfiguration;
use Mail;
use Context;

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

            if (!PSConfiguration::get('PS_CUSTOMER_CREATION_EMAIL')) {
                return true;
            }

            if (empty($password)) {
                $password = str_repeat('*', Tools::strlen(Tools::getValue('passwd')));
            }

            Mail::Send(
                Context::getContext()->language->id,
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

        return $customer;
    }
}
