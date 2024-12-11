<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\AbstractQuery;
use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Table\ReferentialAction;
use Pyncer\Database\Table\ForeignKeyQueryInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\InvalidArgumentException;

abstract class AbstractForeignKeyQuery extends AbstractQuery implements
    ForeignKeyQueryInterface
{
    use TableTrait;

    private ?TableQueryInterface $query;
    private string $name;
    private array $columns;
    private string $referenceTable;
    private array $referenceColumns;
    private ?ReferentialAction $deleteAction;
    private ?ReferentialAction $updateAction;

    final public function __construct(
        ConnectionInterface $connection,
        string $table,
        string $name,
        string ...$columnNames,
    ) {
        parent::__construct($connection);

        // TODO: Verify refTable/refColumns exist?

        $this->setTable($table);
        $this->setName($name);
        $this->setColumns($columnNames);
        $this->setDeleteAction(null);
        $this->setUpdateAction(null);
        $this->setQuery(null);
    }

    public static function fromTableQuery(
        TableQueryInterface $query,
        string $name,
        string ...$columnNames,
    ): static
    {
        $foreignKey = new static(
            $query->getConnection(),
            $query->getTable(),
            $name,
            ...$columnNames
        );

        $foreignKey->setQuery($query);

        return $foreignKey;
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
                'At least one reference column is required for a foreign key. (' . $this->getName() . ')'
            );
        }

        $this->columns = $value;
        return $this;
    }

    public function getReferenceTable(): string
    {
        return $this->referenceTable;
    }
    public function setReferenceTable(string $value): static
    {
        if ($value === '') {
            throw new InvalidArgumentException(
                'Reference table cannot be an empty string. (' . $this->getName() . ')'
            );
        }

        $this->referenceTable = $value;
        return $this;
    }

    public function getReferenceColumns(): array
    {
        return $this->referenceColumns;
    }
    public function setReferenceColumns(array $value): static
    {
        if (!$value) {
            throw new InvalidArgumentException(
                'At least one reference column is required for a foreign key. (' . $this->getName() . ')'
            );
        }

        $this->referenceColumns = $value;
        return $this;
    }

    public function getDeleteAction(): ?ReferentialAction
    {
        return $this->deleteAction;
    }
    public function setDeleteAction(?ReferentialAction $value): static
    {
        $this->deleteAction = $value;
        return $this;
    }

    public function getUpdateAction(): ?ReferentialAction
    {
        return $this->updateAction;
    }
    public function setUpdateAction(?ReferentialAction $value): static
    {
        $this->updateAction = $value;
        return $this;
    }

    public function equals(mixed $value): bool
    {
        if (!$value instanceof ForeignKeyQueryInterface) {
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

        if ($this->getReferenceTable() !== $value->getReferenceTable()) {
            return false;
        }

        if ($this->getReferenceColumns() !== $value->getReferenceColumns()) {
            return false;
        }

        if ($this->getDeleteAction() !== $value->getDeleteAction()) {
            return false;
        }

        if ($this->getUpdateAction() !== $value->getUpdateAction()) {
            return false;
        }

        return true;
    }
}
