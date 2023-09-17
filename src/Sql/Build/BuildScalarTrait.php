<?php
namespace Pyncer\Database\Sql\Build;

use DateTimeInterface;
use Pyncer\Exception\InvalidArgumentException;
use Stringable;

trait BuildScalarTrait
{
    private function buildScalar(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return strval($value);
        }

        if (is_bool($value)) {
            return ($value ? "'1'" : "'0'");
        }

        if ($value instanceof DateTimeInterface) {
            $value = $this->getConnection()->dateTime($value);
        }

        if (is_scalar($value) || $value instanceof Stringable) {
            $value = strval($value);
        } else {
            throw new InvalidArgumentException('The specified value is not supported.');
        }

        return "'" . $this->getConnection()->escapeString($value) . "'";
    }
}
