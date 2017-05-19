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
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }
}
