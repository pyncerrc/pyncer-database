<?php
namespace Pyncer\Database\Sql\Table;

use Pyncer\Database\EncodingInterface;
use Pyncer\Database\Exception\ResultException;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Table\TableQueryTrait;
use Pyncer\Database\Table\AbstractCreateTableQuery;
use Pyncer\Database\Table\Column\IntColumnQueryInterface;
use Pyncer\Database\Table\Column\DateTimeColumnQueryInterface;
use Pyncer\Database\Table\Column\IntSize;
use Pyncer\Database\Table\Column\TextSize;

class CreateTableQuery extends AbstractCreateTableQuery
{
    use BuildColumnTrait;
    use BuildScalarTrait;
    use BuildTableTrait;
    use TableQueryTrait;

    public function execute(?array $params = null): bool|array|object
    {
        $params['multi'] = true;

        /** @var array<int, mixed> */
        $result = $this->getConnection()->execute(
            $this->getQueryString(),
            $params
        );

        // Only return false if table creation fails
        if ($result[0] === false) {
            return false;
        }

        return $result;
    }

    public function getQueryString(): string
    {
        $connection = $this->getConnection();

        $query = 'CREATE TABLE ' . $this->buildTable($this->getTable()) . ' (';

        $queryParts = [];

        foreach ($this->columns as $column) {
            $queryParts[] = $this->buildColumn($column->getName()) .
                ' ' . $column->getDefinitionQueryString();
        }

        // If we have a primary key, add it
        if ($this->primary) {
            $primary = array_map(function($value) {
                return $this->buildColumn($value);
            }, $this->primary);
            $primary = implode(', ', $primary);
            $queryParts[] = 'PRIMARY KEY (' . $primary . ')';
        }

        // Indexes
        foreach ($this->indexes as $index) {
            $queryParts[] = $index->getDefinitionQueryString();
        }

        // Foreign keys
        foreach ($this->foreignKeys as $foreignKey) {
            $queryParts[] = $foreignKey->getDefinitionQueryString();
        }

        // Remove last ', ' and close
        $query .= implode(', ', $queryParts) . ')';

        // Engine
        $query .= ' ENGINE ' . $this->buildScalar($this->getEngine());

        // Encoding
        $query .= ' CHARACTER SET ' . $this->buildScalar($this->getCharacterSet());
        $query .= ' COLLATE ' . $this->buildScalar($this->getCollation());

        $comment = $this->getComment();
        if ($comment !== null) {
            $query .= ' COMMENT ' . $this->buildScalar($comment);
        }

        $query .= ';';

        return $query;
    }
}
