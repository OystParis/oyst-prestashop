<?php

namespace Oyst\Transformer;

/**
 * Interface DataTransformerInterface
 */
interface DataTransformerInterface
{
    public function transform($value);

    public function reverseTransform($value);
}
