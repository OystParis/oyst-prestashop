<?php

namespace Oyst\Controller;

use Configuration;
use Oyst\Classes\FileLogger;

class ScriptTagController extends AbstractOystController
{
    public function _construct()
    {
        $this->logger = new FileLogger();
        $this->logger->setFile(dirname(__FILE__).'/../../logs/script-tag.log');
    }

    public function setUrl($params)
    {
        if (Configuration::updateValue('OYST_SCRIPT_TAG_URL', $params['data']['url'])) {
            $this->respondAsJson('OK');
        } else {
            $this->respondError(400, "Error on update");
        }
    }
}
