<?php

namespace Oyst\Repository;
use Db;

abstract class AbstractOystRepository
{
    /**
     * @var Db
     */
    protected $db;

    /**
     * Oyst\Repository\AbstractOystRepository constructor.
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }
}
