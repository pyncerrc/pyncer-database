<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class LPadFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "LPAD(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
