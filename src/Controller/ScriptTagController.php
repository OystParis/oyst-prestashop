<?php

namespace Oyst\Controller;

use Configuration;

class ScriptTagController extends AbstractOystController
{
    public function setUrl($params)
    {
        if (Configuration::updateValue('OYST_SCRIPT_TAG_URL', $params['data']['url'])) {
            $this->respondAsJson('OK');
        } else {
            $this->respondError(400, "Error on update");
        }
    }
}
