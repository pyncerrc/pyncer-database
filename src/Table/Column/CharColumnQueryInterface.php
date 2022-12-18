<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\EncodingInterface;
use Pyncer\Database\Table\Column\ColumnQueryInterface;

interface CharColumnQueryInterface extends
    ColumnQueryInterface,
    EncodingInterface
{
    public function getLength(): int;
    public function setLength(int $value): static;
}
