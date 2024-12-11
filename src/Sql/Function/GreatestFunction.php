<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class GreatestFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "GREATEST(";

        $params = $this->getArgumentQueryString();
        if ($params === '') {
            $params = '0';
        }

        $query .= $params;

        $query .= ")";

        return $query;
    }
}
