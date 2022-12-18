<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\RecordQueryInterface;

interface GroupByQueryInterface extends RecordQueryInterface
{
    public function groupBy(string|array ...$columns): static;
}
