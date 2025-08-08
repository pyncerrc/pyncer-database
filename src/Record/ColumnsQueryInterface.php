<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\RecordQueryInterface;

interface ColumnsQueryInterface extends RecordQueryInterface
{
    public function columns(string|array ...$columns): static;

    public function hasColumns(): bool;
}
