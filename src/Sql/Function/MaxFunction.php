<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class MaxFunction extends AbstractFunction
{
    protected $distinct;

    public function distinct(): static
    {
        $this->distinct = true;
        return $this;
    }

    public function getQueryString(): string
    {
        $query = "MAX(" . ($this->distinct ? " DISTINCT" : '');

        $params = $this->getArgumentQueryString();
        if ($params === '') {
            $params = '*';
        }

        $query .= $params;

        $query .= ")";

        return $query;
    }
}
