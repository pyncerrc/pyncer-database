<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\QueryInterface;
use Pyncer\Database\Table\CommentInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Utility\EqualsInterface;

interface ColumnQueryInterface extends
    CommentInterface,
    EqualsInterface,
    QueryInterface
{
    public function getQuery(): ?TableQueryInterface;

    public function getTable(): string;

    public function getName(): string;

    public function getNewName(): ?string;
    public function setNewName(?string $value): static;

    public function getFirst(): bool;
    public function setFirst(bool $value): static;

    public function getAfter(): ?string;
    public function setAfter(?string $value): static;

    public function getNull(): bool;
    public function setNull(bool $value): static;

    public function getDefault(): mixed;
    public function setDefault(mixed $value): static;
}
