<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Function\AbstractFunction as BaseAbstractFunction;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildConditionColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;

abstract class AbstractFunction extends BaseAbstractFunction
{
    use BuildTableTrait;
    use BuildColumnTrait;
    use BuildConditionColumnTrait;
    use BuildScalarTrait;

    protected function getArgumentQueryString(string $separator = ','): string
    {
        if (!$this->arguments) {
            return '';
        }

        $arguments = array_map(
            fn($value) => $this->buildConditionColumn($value),
            $this->arguments
        );

        return implode($separator, $arguments);
    }
}
