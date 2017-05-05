<?php

namespace Oyst\Service\Serializer;

use Oyst\Classes\OystProduct;
use Symfony\Component\Serializer\Encoder\scalar;

class ExportProductRequestParamSerializer extends AbstractSerializer
{
    /**
     * @param OystProduct[] $oystProducts
     * @return array|string|scalar
     */
    public function serialize($oystProducts)
    {
        $serializer = $this->getObjectSerializer();

        return $serializer->serialize($oystProducts, 'json');
    }
}
