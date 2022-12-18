<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\Function\AbstractFunction;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildConditionColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;

abstract class AbstractFunction extends AbstractFunction
{
    use BuildTableTrait;
    use BuildColumnTrait;
    use BuildConditionColumnTrait;
    use BuildScalarTrait;

    public function execute(array $params = null): bool|array|object
    {
        return $this->getConnection()->execute($this->getQueryString(), $params);
    }

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
