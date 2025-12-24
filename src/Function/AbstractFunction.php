<?php
namespace Pyncer\Database\Function;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\InvalidArgumentException;
use Stringable;

abstract class AbstractFunction implements FunctionInterface
{
    use ConnectionTrait;
    use TableTrait;

    protected array $arguments = [];

    public function __construct(
        ConnectionInterface $connection,
        string $table
    ) {
        $this->setConnection($connection);
        $this->setTable($table);
    }

    public function arguments(mixed ...$arguments): static
    {
        $this->arguments = [];

        foreach ($arguments as $argument) {
            if (is_string($argument)) {
                $argument = [$this->getTable(), $argument];
            } elseif (is_int($argument) || is_float($argument)) {
                $argument = [null, $argument];
            } elseif ($argument instanceof FunctionInterface) {
                $argument = ['@', $argument];
            } elseif ($argument instanceof Stringable) {
                $argument = [null, strval($argument)];
            }

            if (!is_array($argument)) {
                throw new InvalidArgumentException('Function argument is invalid.');
            }

            $argument = array_values($argument);

            $count = count($argument);
            if ($count !== 2) {
                throw new InvalidArgumentException('Function argument is invalid.');
            }

            if ($argument[0] !== '@' && $argument[1] instanceof FunctionInterface) {
                throw new InvalidArgumentException('Function argument is invalid.');
            }

            $this->arguments[] = [$argument[0], $argument[1]];
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getQueryString();
    }
}
