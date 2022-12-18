<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class RandFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "RAND(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
