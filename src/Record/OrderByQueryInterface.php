<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\RecordQueryInterface;

interface OrderByQueryInterface extends RecordQueryInterface
{
    public function orderBy(null|string|array|FunctionInterface ...$columns): static;
}
