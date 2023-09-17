<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Exception\InvalidArgumentException;
use Stringable;

trait ValuesTrait
{
    protected array $values = [];

    public function values(iterable $values): static
    {
        $this->values = [];

        foreach ($values as $name => $value) {
            if ($value instanceof Stringable &&
                !$value instanceof FunctionInterface
            ) {
                $value = strval($value);
            }

            if ($value === null ||
                is_scalar($value) ||
                $value instanceof FunctionInterface
            ) {
                $this->values[$name] = $value;
            } else {
                throw new InvalidArgumentException('Invalid values specified.');
            }
        }

        return $this;
    }
}
