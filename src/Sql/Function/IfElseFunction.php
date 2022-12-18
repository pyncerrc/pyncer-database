<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class IfElseFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "IF(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
