<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\AbstractQuery;
use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Record\RecordQueryInterface;
use Pyncer\Database\TableTrait;

abstract class AbstractRecordQuery extends AbstractQuery implements
    RecordQueryInterface
{
    use TableTrait;

    public function __construct(ConnectionInterface $connection, string $table)
    {
        parent::__construct($connection);

        $this->setTable($table);
    }
}
