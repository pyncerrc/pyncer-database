<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\QueryInterface;
use Pyncer\Database\Table\CommentInterface;
use Pyncer\Database\Table\TableQueryInterface;
use Pyncer\Utility\EqualsInterface;

interface IndexQueryInterface extends
    CommentInterface,
    EqualsInterface,
    QueryInterface
{
    public function getQuery(): ?TableQueryInterface;

    public function getTable(): string;

    public function getName(): string;

    public function getColumns(): array;

    public function getUnique(): bool;
    public function setUnique(bool $value): static;

    public function getFulltext(): bool;
    public function setFulltext(bool $value): static;

    public function getComment(): ?string;
    public function setComment(?string $value): static;
}
