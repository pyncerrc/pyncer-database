<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\QueryInterface;

interface RecordQueryInterface extends QueryInterface
{
    public function getTable(): string;
}
