<?php

namespace Oyst\Controller;

class CartController extends AbstractOystController
{
    public function getCart($params)
    {
        echo "getCart<pre>";
        print_r($params);
        echo "</pre>";
        exit;
    }

    public function updateCart($params)
    {
        echo "updatCart<pre>";
        print_r($params);
        echo "</pre>";
        exit;
    }
}
