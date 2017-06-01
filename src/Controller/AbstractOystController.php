<?php

namespace Oyst\Controller;

use Oyst;
use Oyst\Service\Http\CurrentRequest;
use Oyst\Service\Logger\AbstractLogger;

abstract class AbstractOystController
{
    /**
     * @var CurrentRequest
     */
    protected $request;

    /** @var  AbstractLogger */
    protected $logger;

    /** @var  Oyst */
    protected $oyst;

    /**
     * Oyst\Controller\AbstractOystController constructor.
     * @param CurrentRequest $request
     */
    public function __construct(CurrentRequest $request)
    {
        $this->request = $request;
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
     * @param $content
     */
    protected function respondAsJson($content)
    {
        header('Content-Type: application/json');
        echo json_encode($content);
    }
}
