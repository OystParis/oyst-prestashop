<?php

namespace Oyst\Controller;

use Customer;
use Language;
use Validate;

class CustomerController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('customer');
    }

    public function getCustomer($params)
    {
        if (!empty($params['url']['id'])) {
            $customer = new Customer($params['url']['id']);
            if (Validate::isLoadedObject($customer)) {
                $result = array();
                $addresses = $customer->getAddresses(Language::getIdByIso('FR'));
                //TODO return object with payload convention

                //TODO Remplacer les getIdByIso('FR') par la langue du client (evnetuellement) => voir avec steph
            } else {
                $this->respondError(400, 'Bad customer id');
            }
        } else {
            $this->respondError(400, 'id_customer is missing');
        }
    }
}
