<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Exception\ColumnExistsException;
use Pyncer\Database\Table\AbstractTableQuery;
use Pyncer\Database\Table\AlterTableQueryInterface;

abstract class AbstractAlterTableQuery extends AbstractTableQuery implements
    AlterTableQueryInterface
{
    protected array $existingColumns;
    protected array $existingIndexes;
    protected array $existingForeignKeys;
    protected array $existingPrimary;
    protected ?string $existingComment;
    protected string $existingEngine;
    protected string $existingCharacterSet;
    protected string $existingCollation;

    public function __construct(ConnectionInterface $connection, string $table)
    {
        parent::__construct($connection, $table);

        $this->existingColumns = $this->initializeExistingColumns();
        $this->existingIndexes = $this->initializeExistingIndexes();
        $this->existingForeignKeys = $this->initializeExistingForeignKeys();
        $this->existingPrimary = $this->initializeExistingPrimary();
        $this->existingComment = $this->initializeExistingComment();
        $this->existingEngine = $this->initializeExistingEngine();
        $this->existingCharacterSet = $this->initializeExistingCharacterSet();
        $this->existingCollation = $this->initializeExistingCollation();

        foreach ($this->existingColumns as $key => $value) {
            $this->columns[$key] = clone $value;
        }

        foreach ($this->existingIndexes as $key => $value) {
            $this->indexes[$key] = clone $value;
        }

        foreach ($this->existingForeignKeys as $key => $value) {
            $this->foreignKeys[$key] = clone $value;
        }

        $this->primary = $this->existingPrimary;

        $this->setComment($this->existingComment);
        $this->setEngine($this->existingEngine);
        $this->setCharacterSet($this->existingCharacterSet);
        $this->setCollation($this->existingCollation);
    }

    public function first(?string $columnName = null): static
    {
        if ($columnName === null) {
            $column = $this->getColumns()[0];
        } else {
            $column = $this->getColumn($columnName);
        }
        $column->setFirst(true);
        return $this;
    }

    public function after(string $afterColumnName, ?string $columnName = null): static
    {
        if ($columnName === null) {
            $column = $this->getColumns()[0];
        } else {
            $column = $this->getColumn($columnName);
        }

        $column->setAfter($afterColumnName);

        return $this;
    }

    public function rename(string $newColumnName, ?string $columnName = null): static
    {
        if ($this->hasColumn($newColumnName)) {
            throw new ColumnExistsException($this->getTable(), $newColumnName);
        }

        if ($columnName === null) {
            $column = $this->getColumns()[0];
        } else {
            $column = $this->getColumn($columnName);
        }

        $column->setNewName($newColumnName);

        return $this;
    }

    protected abstract function initializeExistingColumns(): array;
    protected abstract function initializeExistingIndexes(): array;
    protected abstract function initializeExistingForeignKeys(): array;
    protected abstract function initializeExistingPrimary(): array;
    protected abstract function initializeExistingComment(): ?string;
    protected abstract function initializeExistingEngine(): string;
    protected abstract function initializeExistingCharacterSet(): string;
    protected abstract function initializeExistingCollation(): string;
}
