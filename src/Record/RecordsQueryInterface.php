<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\RecordQueryInterface;

interface RecordsQueryInterface extends RecordQueryInterface
{
    public function getTables(): array;
}
