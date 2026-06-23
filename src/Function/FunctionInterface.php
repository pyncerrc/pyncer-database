<?php
namespace Pyncer\Database\Function;

use Pyncer\Database\QueryStringInterface;

interface FunctionInterface extends QueryStringInterface
{
    public function getTable(): string;
}
