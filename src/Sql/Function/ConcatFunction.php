<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class ConcatFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "CONCAT(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
