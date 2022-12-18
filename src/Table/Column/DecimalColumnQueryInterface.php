<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\Column\UnsignedColumnInterface;

interface DecimalColumnQueryInterface extends ColumnQueryInterface
{
    public function getPrecision(): int;
    public function setPrecision(int $value): static;

    public function getScale(): int;
    public function setScale(int $value): static;
}
