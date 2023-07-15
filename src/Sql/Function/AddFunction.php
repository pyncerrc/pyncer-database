<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;

class AddFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "(";

        $query .= $this->getArgumentQueryString('+');

        $query .= ")";

        return $query;
    }

    protected function buildScalar($value): int|float
    {
        if (strpos($value, '.') === false) {
            return intval($value);
        }

        return floatval($value);
    }
}
