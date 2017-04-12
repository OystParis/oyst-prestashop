<?php

/**
 * Class AbstractOystService
 */
abstract class AbstractOystService
{
    /**
     * @var Context
     */
    protected $context;
    /**
     * @var AbstractOystRepository
     */
    protected $repository;
    /**
     * @var Oyst
     */
    protected $oyst;

    /**
     * AbstractOystService constructor.
     * @param Context $context
     * @param Oyst $oyst
     */
    public function __construct(Context $context, Oyst $oyst)
    {
        $this->context = $context;
        $this->oyst = $oyst;
    }

    /**
     * @param AbstractOystRepository $repository
     * @return AbstractOystService
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @return AbstractOystRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return Oyst
     */
    public function getOyst()
    {
        return $this->oyst;
    }
}
