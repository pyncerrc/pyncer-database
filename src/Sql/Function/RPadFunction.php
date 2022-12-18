<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class RPadFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "RPAD(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
