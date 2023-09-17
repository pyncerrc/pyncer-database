<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildConditionColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\InvalidArgumentException;
use Pyncer\Exception\UnexpectedValueException;

class CaseWhenFunction implements FunctionInterface
{
    use ConnectionTrait;
    use TableTrait;
    use BuildTableTrait;
    use BuildColumnTrait;
    use BuildConditionColumnTrait;
    use BuildScalarTrait;

    protected ?string $case = null;
    protected ?string $else = null;
    protected array $conditions = [];

    public function __construct(
        ConnectionInterface $connection,
        string $table
    ) {
        $this->setConnection($connection);
        $this->setTable($table);
    }

    public function execute(?array $params = null): bool|array|object
    {
        return $this->getConnection()->execute($this->getQueryString(), $params);
    }

    public function case(mixed $column, bool $binary = false): static
    {
        $case = $this->buildConditionColumn($column);

        if ($binary) {
            $case = 'BINARY ' . $case;
        }

        $this->case = $case;

        return $this;
    }

    public function when(mixed $value, mixed $result): static
    {
        if ($this->case === null) {
            throw new UnexpectedValueException('Case not set.');
        }

        if ($value === null) {
            $operator = 'IS';
        } else {
            $operator = '=';
        }

        $value = $this->buildScalar($value);
        $result = $this->buildScalar($result);

        $this->conditions[] = [
            $this->case . ' ' . $operator . ' ' . $value,
            $result
        ];

        return $this;
    }

    public function whenCompare(
        mixed $column,
        mixed $value,
        mixed $result,
        string $operator = '=',
        bool $binary = false
    ): static
    {
        $column = $this->buildConditionColumn($column);

        if ($binary) {
            $column = 'BINARY ' . $column;
        }

        if ($value === null) {
            if ($operator == '=') {
                $operator = 'IS';
            } elseif ($operator == '!=') {
                $operator = 'IS NOT';
            } else {
                throw new InvalidArgumentException('Invalid operator for NULL value.');
            }
        }

        $value = $this->buildScalar($value);
        $result = $this->buildScalar($result);

        $this->conditions[] = [
            $column . ' ' . $operator . ' ' . $value,
            $result
        ];

        return $this;
    }

    public function else(mixed $result): static
    {
        $this->else = $this->buildScalar($result);
        return $this;
    }

    public function getQueryString(): string
    {
        if (!$this->conditions) {
            if ($this->else === null) {
                return 'NULL';
            }

            return $this->else;
        }

        $query = 'CASE';

        foreach ($this->conditions as $condition) {
            $query .= ' WHEN ' . $condition[0] . ' THEN ' . $condition[1];
        }

        if ($this->else !== null) {
            $query .= ' ELSE ' . $this->else;
        }

        $query .= ' END';

        return $query;
    }

    public function __toString(): string
    {
        return $this->getQueryString();
    }
}
