<?php
namespace Pyncer\Database\Driver\MySql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class STDistanceSphereFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "ST_Distance_Sphere(";

        $query .= $this->getArgumentQueryString();

        $query .= ")";

        return $query;
    }
}
