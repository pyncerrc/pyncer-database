<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\Column\IntSize;

interface IntColumnQueryInterface extends ColumnQueryInterface
{
    public function getSize(): IntSize;
    public function setSize(IntSize $value): static;

    public function getAutoIncrement(): bool;
    public function setAutoIncrement(bool $value): static;

    public function getUnsigned(): bool;
    public function setUnsigned(bool $value): static;
}
