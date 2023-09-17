<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\QueryInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Database\Table\ReferentialAction;
use Pyncer\Utility\EqualsInterface;

interface ForeignKeyQueryInterface extends QueryInterface, EqualsInterface
{
    public function getQuery(): ?TableQueryInterface;

    public function getTable(): string;

    public function getName(): string;

    public function getColumns(): array;

    public function getReferenceTable(): string;
    public function setReferenceTable(string $value): static;

    public function getReferenceColumns(): array;
    public function setReferenceColumns(array $value): static;

    public function getDeleteAction(): ?ReferentialAction;
    public function setDeleteAction(?ReferentialAction $value): static;

    public function getUpdateAction(): ?ReferentialAction;
    public function setUpdateAction(?ReferentialAction $value): static;

    public function equals(mixed $value): bool;
}
