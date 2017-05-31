<?php

namespace Oyst\Service;

use Context;
use Oyst;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\AbstractLogger;
use Oyst\Service\Serializer\SerializerInterface;

/**
 * Class AbstractOystService
 * @package Oyst\Service
 */
abstract class AbstractOystService
{
    /** @var Context  */
    protected $context;

    /** @var Oyst  */
    protected $oyst;

    /** @var  AbstractLogger */
    protected $logger;

    /** @var  SerializerInterface */
    protected $serializer;

    /** @var  Requester */
    protected $requester;

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

    /**
     * @param AbstractLogger $logger
     * @return $this
     */
    public function setLogger(AbstractLogger $logger)
    {
        $this->logger = $logger;

        return $this;
    }


    /**
     * @param SerializerInterface $serializer
     * @return $this
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @param Requester $requester
     * @return $this
     */
    public function setRequester(Requester $requester)
    {
        $this->requester = $requester;

        return $this;
    }

    /**
     * @return Requester
     */
    public function getRequester()
    {
        return $this->requester;
    }
}
