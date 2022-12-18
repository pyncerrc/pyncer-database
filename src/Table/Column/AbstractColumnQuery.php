<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Exception\ColumnExistsException;
use Pyncer\Database\Exception\ColumnNotFoundException;
use Pyncer\Database\AbstractQuery;
use Pyncer\Database\TableTrait;
use Pyncer\Database\Table\CommentTrait;
use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Database\Value;

abstract class AbstractColumnQuery extends AbstractQuery implements
    ColumnQueryInterface
{
    use CommentTrait;
    use TableTrait;

    private ?TableQueryInterface $query;
    private string $name;
    private ?string $newName;
    private bool $first;
    private ?string $after;
    private bool $nullable;
    private mixed $default;

    public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name
    ) {
        parent::__construct($connection);

        $this->setTable($table);
        $this->setName($name);
        $this->setNewName(null);
        $this->setFirst(false);
        $this->setAfter(null);
        $this->setNull(false);
        $this->setDefault(Value::NONE);
        $this->setComment(null);
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
        $this->name = $value;
        return $this;
    }

    public function getNewName(): ?string
    {
        return $this->newName;
    }
    public function setNewName(?string $value): static
    {
        $this->newName = ($value === '' ? null : $value);
        return $this;
    }

    public function getFirst(): bool
    {
        return $this->first;
    }
    public function setFirst(bool $value): static
    {
        $this->first = $value;

        if ($this->first) {
            $this->after = null;
        }

        return $this;
    }

    public function getAfter(): ?string
    {
        return $this->after;
    }
    public function setAfter(?string $value): static
    {
        $this->after = ($value === '' ? null : $value);

        if ($this->after !== null) {
            $this->first = false;
        }

        return $this;
    }

    public function getNull(): bool
    {
        return $this->nullable;
    }
    public function setNull(bool $value): static
    {
        $this->nullable = $value;
        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }
    public function setDefault(mixed $value): static
    {
        $this->default = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof ColumnQueryInterface) {
            return false;
        }

        if ($this->getTable() !== $value->getTable()) {
            return false;
        }

        if ($this->getName() !== $value->getName()) {
            return false;
        }

        if ($this->getNewName() !== $value->getNewName()) {
            return false;
        }

        if ($this->getFirst() !== $value->getFirst()) {
            return false;
        }

        if ($this->getAfter() !== $value->getAfter()) {
            return false;
        }

        if ($this->getNull() !== $value->getNull()) {
            return false;
        }

        if ($this->getDefault() !== $value->getDefault()) {
            return false;
        }

        if ($this->getComment() !== $value->getComment()) {
            return false;
        }

        return true;
    }
}
