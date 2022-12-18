<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\AbstractRecordQuery;
use Pyncer\Database\Record\InsertQueryInterface;
use Pyncer\Database\Record\ValuesTrait;

abstract class AbstractInsertQuery extends AbstractRecordQuery implements
    InsertQueryInterface
{
    use ValuesTrait;

    protected bool $ignore = false;

    public function ignore(): static
    {
        $this->ignore = true;
        return $this;
    }
}
