<?php

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

    /**
     * ExportProductController constructor.
     * @param ExportProductService $exportProductService
     * @param OystCatalogAPI $oystCatalogAPI
     */
    public function __construct(ExportProductService $exportProductService, OystCatalogAPI $oystCatalogAPI)
    {
        $this->exportProductService = $exportProductService;
        $this->oystCatalogAPI = $oystCatalogAPI;
    }

    public function run()
    {
        $this->exportProductService->setCatalogApi($this->oystCatalogAPI);
        $this->exportProductService->setWeightUnit(ConfigurationCore::get('PS_WEIGHT_UNIT'));
        $this->exportProductService->setDimensionUnit(ConfigurationCore::get('PS_CURRENCY_DEFAULT'));

        // TODO: Handle importId properly when the process will be available
        $this->exportProductService->export(rand());
    }
}
