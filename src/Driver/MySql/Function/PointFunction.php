<?php
namespace Pyncer\Database\Driver\MySql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class PointFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "POINT(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
