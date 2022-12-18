<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\Column\FloatSize;

interface FloatColumnQueryInterface extends ColumnQueryInterface
{
    public function getSize(): FloatSize;
    public function setSize(FloatSize $value): static;
}
