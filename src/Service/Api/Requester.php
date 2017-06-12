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

namespace Oyst\Service\Api;

use Guzzle\Http\Message\Response;
use Oyst\Api\AbstractOystApiClient;
use Oyst\Service\Logger\AbstractLogger;
use Oyst\Service\Logger\LogLevel;
use Oyst\Service\Serializer\SerializerInterface;
use Tools;

class Requester
{
    /**
     * @var AbstractOystApiClient
     */
    private $apiClient;

    /**
     * @var AbstractLogger
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(AbstractOystApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @param string $method
     * @param array $params
     * @return Response
     */
    public function call($method, $params = array())
    {
        /** @var Response $result */
        $result = call_user_func_array(array($this->apiClient, $method), $params);
        //dump($result, $method, $params); die();

        if ($this->logger instanceof AbstractLogger) {
            $thisCallArgs = null == $params ? null : func_get_arg(1);

            $messageMask = 'Request from %s %s. HTTP[%s] BODY[%s]';
            $context = array(
                'objectType' => 'OystRequest'
            );

            $paramsEncoded =  null == $thisCallArgs ? '' : Tools::substr(
                $this->serializer instanceof SerializerInterface ?
                    $this->serializer->serialize($params) :
                    json_encode($params, JSON_OBJECT_AS_ARRAY),
                0,
                255
            );

            $requestFrom = sprintf(
                '%s::%s(%s)',
                get_class($this->logger),
                $method,
                $paramsEncoded
            );

            if ($this->apiClient->getLastHttpCode() == 200) {
                $messageState = 'Succeed';
                $method = LogLevel::INFO;
            } else {
                $messageState = 'Failed';
                $method = LogLevel::EMERGENCY;
            }

            $message = sprintf(
                $messageMask,
                $requestFrom,
                $messageState,
                $this->apiClient->getLastHttpCode(),
                $this->apiClient->getBody()
            );

            call_user_func(array($this->logger, $method), $message, $context);
        }

        return $result;
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
     * @return AbstractOystApiClient
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @param AbstractOystApiClient $apiClient
     * @return $this
     */
    public function setApiClient(AbstractOystApiClient $apiClient)
    {
        $this->apiClient = $apiClient;

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
}
