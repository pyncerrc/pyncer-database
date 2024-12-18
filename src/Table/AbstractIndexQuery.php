<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\AbstractQuery;
use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\CommentTrait;
use Pyncer\Database\Table\IndexQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\InvalidArgumentException;

abstract class AbstractIndexQuery extends AbstractQuery implements
    IndexQueryInterface
{
    use CommentTrait;
    use TableTrait;

    private ?TableQueryInterface $query;
    private string $name;
    private array $columns;
    private bool $unique;
    private bool $fulltext;

    final public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        string ...$columnNames,
    ) {
        parent::__construct($connection);

        $this->setTable($table);
        $this->setName($name);
        $this->setColumns($columnNames);
        $this->setUnique(false);
        $this->setFulltext(false);
        $this->setComment(null);
        $this->setQuery(null);
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        string ...$columnNames,
    ): static
    {
        $index = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            ...$columnNames
        );

        $index->setQuery($query);

        return $index;
    }

    public function getQuery(): ?TableQueryInterface
    {
        return $this->query;
    }
    protected function setQuery(?TableQueryInterface $value): static
    {
        $this->query = $value;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
    protected function setName(string $value): static
    {
        if ($value === '') {
            throw new InvalidArgumentException(
                'Name cannot be an empty string.'
            );
        }

        $this->name = $value;
        return $this;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
    protected function setColumns(array $value): static
    {
        if (!$value) {
            throw new InvalidArgumentException(
                'At least one column is required for an index. (' . $this->getName() . ')'
            );
        }

        $this->columns = $value;
        return $this;
    }

    public function getUnique(): bool
    {
        return $this->unique;
    }
    public function setUnique(bool $value): static
    {
        $this->unique = $value;
        $this->fulltext = false;
        return $this;
    }

    public function getFulltext(): bool
    {
        return $this->fulltext;
    }
    public function setFulltext(bool $value): static
    {
        $this->fulltext = $value;
        $this->unique = false;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof IndexQueryInterface) {
            return false;
        }

        if ($this->getTable() !== $value->getTable()) {
            return false;
        }

        if ($this->getName() !== $value->getName()) {
            return false;
        }

        if ($this->getColumns() !== $value->getColumns()) {
            return false;
        }

        if ($this->getUnique() !== $value->getUnique()) {
            return false;
        }

        if ($this->getFulltext() !== $value->getFulltext()) {
            return false;
        }

        if ($this->getComment() !== $value->getComment()) {
            return false;
        }

        return true;
    }
}
