<?php

namespace Oyst\Controller;


class InformationController extends AbstractOystController
{
    public function __construct()
    {
        parent::__construct();
        $this->setLogName('information');
    }

    public function getInformation($params)
    {
        //All log
        if (empty($params['url'])) {
            $files = glob($this->logs_path.'*.log');

        } else {

        }
    }

    private function readLines($filepath, $n)
    {
        //Return the last $n lines
        $file = fopen($filepath, 'R');
        while (fread($file, 1000)) {

        }
    }
}
