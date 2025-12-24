<?php
namespace Pyncer\Database\Expression;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\Expression\ExpressionInterface;
use Pyncer\Exception\InvalidArgumentException;
use Stringable;

abstract class AbstractExpression implements ExpressionInterface
{
    use ConnectionTrait;

    public function __construct(
        ConnectionInterface $connection,
    ) {
        $this->setConnection($connection);
    }

    public function __toString(): string
    {
        return $this->getQueryString();
    }
}
