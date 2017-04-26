<?php

namespace Oyst\Service\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class AbstractSerializer
 * @package Oyst\Service\Serializer
 */
abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * @return Serializer
     */
    public function getObjectSerializer()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer;
    }
}
