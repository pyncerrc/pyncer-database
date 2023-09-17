<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Sql\Function\AbstractFunction;
use Pyncer\Exception\InvalidArgumentException;
use Stringable;

class SubtractFunction extends AbstractFunction
{
    public function getQueryString(): string
    {
        $query = "(";

        $query .= $this->getArgumentQueryString('-');

        $query .= ")";

        return $query;
    }

    protected function buildScalar(mixed $value): int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_scalar($value) || $value instanceof Stringable) {
            $value = strval($value);
        } else {
            throw new InvalidArgumentException('Value is not supported.');
        }

        if (strpos($value, '.') === false) {
            return intval($value);
        }

        return floatval($value);
    }
}
