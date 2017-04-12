<?php

namespace Oyst\Service;

use Context;
use Oyst;

/**
 * Class AbstractOystService
 * @package Oyst\Service
 */
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

    /**
     * @return Oyst
     */
    public function getOyst()
    {
        return $this->oyst;
    }
}
