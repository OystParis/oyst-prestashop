<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

namespace Oyst\Controller;

use Oyst;
use Oyst\Service\Http\CurrentRequest;
use Psr\Log\AbstractLogger;

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
        http_response_code(200);
    }
}
