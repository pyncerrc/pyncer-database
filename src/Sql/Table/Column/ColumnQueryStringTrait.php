<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Exception\ColumnExistsException;
use Pyncer\Database\Exception\ColumnNotFoundException;
use Pyncer\Database\EncodingInterface;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\Column\IntColumnQueryInterface;
use Pyncer\Database\Table\Column\DateTimeColumnQueryInterface;
use Pyncer\Database\Value;

trait ColumnQueryStringTrait
{
    use BuildColumnTrait;
    use BuildTableTrait;
    use BuildScalarTrait;

    abstract public function buildType(): string;

    public function getQueryString(): string
    {
        $query = 'ALTER TABLE ' . $this->buildTable($this->getTable());

        if ($this->getNewName() !== null) {
            if ($this->getConnection()->hasColumn($this->getTable(), $this->getNewName())) {
                throw new ColumnExistsException($this->getTable(), $this->getNewName());
            }

            if (!$this->getConnection()->hasColumn($this->getTable(), $this->getName())) {
                throw new ColumnNotFoundException($this->getTable(), $this->getNewName());
            }

            $query .= ' ' . $this->getAlterQueryString();
        }

        if ($this->getConnection()->hasColumn($this->getTable(), $this->getName())) {
            $query .= ' ' . $this->getAlterQueryString();
        } else {
            $query .= ' ' . $this->getCreateQueryString();
        }

        $query .= ';';

        return $query;
    }

    public function getCreateQueryString(): string
    {
        return 'ADD COLUMN ' . $this->buildColumn($this->getName()) .
            ' ' . $this->getDefinitionQueryString() .
            ($this->getFirst() ? ' FIRST' : '') .
            ($this->getAfter() ? ' AFTER ' . $this->buildColumn($this->getAfter()) : '');
    }

    public function getAlterQueryString(): string
    {
        // In case comment was previously set
        $commenQuery = '';
        if ($this->getComment() === null) {
            $commenQuery = ' COMMENT ' . $this->buildScalar('');
        }

        return (
                $this->getNewName() !== null ?
                ' CHANGE COLUMN ' . $this->buildColumn($this->getName()) . ' ' . $this->buildColumn($this->getNewName()) :
                ' MODIFY COLUMN ' . $this->buildColumn($this->getName())
            ) .
            ' ' . $this->getDefinitionQueryString() .
            $commenQuery .
            ($this->getFirst() ? ' FIRST' : '') .
            ($this->getAfter() ? ' AFTER ' . $this->buildColumn($this->getAfter()) : '');
    }

    public function getDefinitionQueryString(): string
    {
        $query = $this->buildType();

        if ($this instanceof EncodingInterface) {
            $query .= ' CHARACTER SET ' . $this->buildScalar($this->getCharacterSet());
            $query .= ' COLLATE ' . $this->buildScalar($this->getCollation());
        }

        if ($this instanceof IntColumnQueryInterface) {
            $query .= ($this->getAutoIncrement() ? ' AUTO_INCREMENT' : '');
            $query .= ($this->getUnsigned() ? ' UNSIGNED' : '');
        }

        $query .= ($this->getNull() ? ' NULL' : ' NOT NULL');

        $default = $this->getDefault();
        if ($default === Value::NOW) {
            $query .= ' DEFAULT CURRENT_TIMESTAMP';
        } elseif ($default !== Value::NONE) {
            $query .= ' DEFAULT ' . $this->buildScalar($default);
        }

        if ($this instanceof DateTimeColumnQueryInterface) {
            if ($this->getAutoUpdate()) {
                $query .= ' ON UPDATE CURRENT_TIMESTAMP';
            }
        }

        $comment = $this->getComment();
        if ($comment !== null) {
            $query .= ' COMMENT ' . $this->buildScalar($comment);
        }

        return $query;
    }
}
