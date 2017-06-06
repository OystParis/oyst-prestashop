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

namespace Oyst\Service;

use Context;
use Oyst;
use Oyst\Service\Api\Requester;
use Oyst\Service\Logger\AbstractLogger;
use Oyst\Service\Serializer\SerializerInterface;

/**
 * Class AbstractOystService
 */
abstract class AbstractOystService
{
    /** @var Context  */
    protected $context;

    /** @var Oyst  */
    protected $oyst;

    /** @var AbstractLogger */
    protected $logger;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var Requester */
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
