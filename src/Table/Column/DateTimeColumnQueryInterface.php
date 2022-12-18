<?php
namespace Pyncer\Database\Table\Column;

use Pyncer\Database\Table\Column\ColumnQueryInterface;

interface DateTimeColumnQueryInterface extends ColumnQueryInterface
{
    public function getPrecision(): int;
    public function setPrecision(int $value): static;

    public function getAutoUpdate(): bool;
    public function setAutoUpdate(bool $value): static;
}
