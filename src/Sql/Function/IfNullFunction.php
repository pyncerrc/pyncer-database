<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class IfNullFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "IFNULL(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
