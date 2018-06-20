<?php

class Dispatcher extends DispatcherCore
{
    protected function __construct()
    {
        $this->default_routes['oyst_rule'] = array(
            'controller' => 'dispatcher',
            'rule' => 'oyst-oneclick/{request}',
            'keywords' => array(
                'request' => array('regexp' => '.*', 'param' => 'request'),
            ),
            'params' => array(
                'fc' => 'module',
                'module' => 'oyst'
            ),
        );
        parent::__construct();
    }
}
