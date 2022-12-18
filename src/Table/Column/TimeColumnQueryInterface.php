<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\Table\Column\ColumnQueryInterface;

interface TimeColumnQueryInterface extends ColumnQueryInterface
{
    public function getPrecision(): int;
    public function setPrecision(int $value): static;
}
