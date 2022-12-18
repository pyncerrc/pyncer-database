<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\RecordQueryInterface;
use Pyncer\Database\Record\ValuesQueryInterface;

interface InsertQueryInterface extends
    ValuesQueryInterface,
    RecordQueryInterface
{
    public function ignore(): static;
}
