<?php

namespace Oyst\Controller;

class CustomerController extends AbstractOystController
{
    public function search($params)
    {
        echo "search<pre>";
        print_r($params);
        echo "</pre>";
        exit;
    }
}
