<?php

namespace Oyst\Service;

use Oyst\Repository\AbstractOystRepository;
use Context;
use Oyst;

/**
 * Class Oyst\Service\AbstractOystService
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
