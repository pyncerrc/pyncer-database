<?php
namespace Pyncer\Database\Sql\Table;

use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Table\AbstractIndexQuery;

class IndexQuery extends AbstractIndexQuery
{
    use BuildColumnTrait;
    use BuildScalarTrait;
    use BuildTableTrait;

    public function getQueryString(): string
    {
        $name = $this->buildColumn($this->getName());
        $table = $this->buildTable($this->getTable());
        $columns = array_map(array($this, 'buildColumn'), $this->getColumns());
        $columns = implode(', ', $columns);

        $query = 'CREATE';

        if ($this->getUnique()) {
            $query .= ' UNIQUE';
        } elseif ($this->getFulltext()) {
            $query .= ' FULLTEXT';
        }

        $query .= ' INDEX ' . $name . ' ON ' . $table  . ' (' . $columns . ')';

        $comment = $this->getComment();
        if ($comment !== null) {
            $query .= ' COMMENT ' . $this->buildScalar($comment);
        }

        $query .= ';';

        return $query;
    }

    public function getDefinitionQueryString(): string
    {
        $name = $this->buildColumn($this->getName());
        $columns = array_map(array($this, 'buildColumn'), $this->getColumns());
        $columns = implode(', ', $columns);

        $query = '';

        if ($this->getUnique()) {
            $query .= 'UNIQUE ';
        } elseif ($this->getFulltext()) {
            $query .= 'FULLTEXT ';
        }

        $query .= 'INDEX ' . $name . ' (' . $columns . ')';

        $comment = $this->getComment();
        if ($comment !== null) {
            $query .= ' COMMENT ' . $this->buildScalar($comment);
        }

        return $query;
    }
}
