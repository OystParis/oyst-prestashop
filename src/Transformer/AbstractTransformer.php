<?php

namespace Oyst\Transformer;
use Context;

/**
 * Class Oyst\Transformer\AbstractTransformer
 */
abstract class AbstractTransformer implements DataTransformerInterface
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }
}
