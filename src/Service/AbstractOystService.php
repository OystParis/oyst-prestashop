<?php

namespace Oyst\Service;

use Context;
use Oyst;

abstract class AbstractOystService
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Oyst
     */
    protected $oyst;

    /**
     * Oyst\Service\AbstractOystService constructor.
     * @param Context $context
     * @param Oyst $oyst
     */
    public function __construct(Context $context, Oyst $oyst)
    {
        $this->context = $context;
        $this->oyst = $oyst;
    }
}
