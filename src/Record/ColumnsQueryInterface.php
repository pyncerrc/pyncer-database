<?php
namespace pyncer\database\Record;

use Pyncer\Database\Record\RecordQueryInterface;

interface ColumnsQueryInterface extends RecordQueryInterface
{
    public function columns(string|array ...$columns): static;
}
