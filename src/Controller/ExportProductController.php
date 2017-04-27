<?php

namespace Oyst\Controller;

use ConfigurationCore;
use Oyst\Service\ExportProductService;
use Oyst\Api\OystCatalogApi;
use Oyst\Service\Serializer\ExportProductRequestParamSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExportProductController
{
    /**
     * @var ExportProductService
     */
    private $exportProductService;

    /**
     * @var OystCatalogAPI
     */
    private $oystCatalogAPI;


    /** @var  array */
    private $requestData;


    /**
     * Oyst\Controller\ExportProductController constructor.
     * @param ExportProductService $exportProductService
     * @param OystCatalogAPI $apiClient
     */
    public function __construct(ExportProductService $exportProductService, OystCatalogAPI $apiClient)
    {
        $this->exportProductService = $exportProductService;
        $this->oystCatalogAPI = $apiClient;
        $this->requestData = [];
    }

    /**
     * @param $data
     * @return $this
     */
    public function setRequestData($data)
    {
        $this->requestData = $data;

        return $this;
    }

    /**
     * @return JsonResponse
     *
     */
    public function run()
    {
        $importId = $this->requestData['data']['import_id'];
        $state = $this->exportProductService
            ->setCatalogApi($this->oystCatalogAPI)
            ->setWeightUnit(ConfigurationCore::get('PS_WEIGHT_UNIT'))
            ->setSerializer(new ExportProductRequestParamSerializer())
            ->setDimensionUnit(ConfigurationCore::get('PS_CURRENCY_DEFAULT'))
            ->export($importId)
        ;

        $jsonResponse = new JsonResponse();

        $json = [
            'totalCount' => 0,
            'remaining' => 0,
            'httpCode' => $this->oystCatalogAPI->getLastHttpCode(),
            'error' => $this->oystCatalogAPI->getLastError(),
            'state' => $state,
        ];

        if ($state) {

            $json['totalCount'] = $this->exportProductService->getTotalProducts();
            $json['remaining'] = $this->exportProductService->getTotalProductsRemaining();

        }

        return $jsonResponse->setData($json);
    }
}
