<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\EncodingInterface;
use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\Column\TextSize;

interface TextColumnQueryInterface extends
    ColumnQueryInterface,
    EncodingInterface
{
    public function getSize(): TextSize;
    public function setSize(TextSize $value): static;
}
