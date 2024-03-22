<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class CoalesceFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "COALESCE(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
