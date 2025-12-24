<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\Expression\ExpressionInterface;
use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\SearchMode;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildConditionColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\UnexpectedValueException;

class MatchAgainstFunction implements FunctionInterface
{
    use ConnectionTrait;
    use TableTrait;
    use BuildTableTrait;
    use BuildColumnTrait;
    use BuildConditionColumnTrait;
    use BuildScalarTrait;

    protected ?string $column = null;
    protected ?string $value = null;
    protected SearchMode $searchMode = SearchMode::NATURAL_LANGUAGE;

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

    public function match(mixed $column): static
    {
        // Support for multiple columns
        if (is_array($column) && is_array($column[0])) {
            $columns = [];

            foreach ($column as $columnValue) {
                $columns[] = $this->buildConditionColumn($columnValue);
            }

            $columns = implode(',', $columns);
        } else {
            $columns = $this->buildConditionColumn($column);
        }

        $this->column = $columns;

        return $this;
    }

    public function against(
        mixed $value,
        SearchMode $searchMode = SearchMode::NATURAL_LANGUAGE,
    ): static
    {
        if ($value === null) {
            $value = '';
        } elseif ($value instanceof ExpressionInterface) {
            $value = '\'' . $value->getQueryString() . '\'';
        } else {
            $value = $this->buildScalar($value);
        }

        $this->value = $value;

        $this->searchMode = $searchMode;

        return $this;
    }

    public function getQueryString(): string
    {
        $column = $this->column;
        if ($column === null) {
            throw new UnexpectedValueException('Match column not specified.');
        }

        $value = $this->value;
        if ($value === null) {
            throw new UnexpectedValueException('Against value not specified.');
        }

        $mode = match ($this->searchMode) {
            SearchMode::BOOLEAN => 'IN BOOLEAN MODE',
            SearchMode::NATURAL_LANGUAGE => 'IN NATURAL LANGUAGE MODE',
            SearchMode::QUERY_EXPANSION => 'WITH QUERY EXPANSION',
            SearchMode::NATURAL_LANGUAGE_WITH_QUERY_EXPANSION => 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION',
        };

        return 'MATCH(' . $column . ') AGAINST(' . $value . ' ' . $mode . ')';
    }

    public function __toString(): string
    {
        return $this->getQueryString();
    }
}
