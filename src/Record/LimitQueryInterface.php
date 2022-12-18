<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\RecordQueryInterface;

interface LimitQueryInterface extends RecordQueryInterface
{
    public function limit(int $count, int $offset = 0): static;
}
