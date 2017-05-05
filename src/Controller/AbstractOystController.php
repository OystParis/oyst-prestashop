<?php

namespace Oyst\Controller;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractOystController
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * Oyst\Controller\AbstractOystController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
