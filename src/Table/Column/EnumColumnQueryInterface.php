<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\Table\Column\ColumnQueryInterface;

interface EnumColumnQueryInterface extends ColumnQueryInterface
{
    public function getValues(): array;
    public function setValues(array $value): static;
}
