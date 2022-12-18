<?php
namespace pyncer\database\Record;

use Pyncer\Database\Record\RecordQueryInterface;

interface ValuesQueryInterface extends RecordQueryInterface
{
    public function values(iterable $values);
}
