<?php

namespace Oyst\Controller;

use Oyst\Service\Http\CurrentRequest;

abstract class AbstractOystController
{
    /**
     * @var CurrentRequest
     */
    protected $request;

    /**
     * Oyst\Controller\AbstractOystController constructor.
     * @param CurrentRequest $request
     */
    public function __construct(CurrentRequest $request)
    {
        $this->request = $request;
    }
}
