<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Record\AbstractRecordQuery;
use Pyncer\Database\Record\MultipleTableQueryInterface;
use Pyncer\Database\TablesTrait;

abstract class AbstractRecordsQuery extends AbstractRecordQuery implements
    RecordsQueryInterface
{
    use TablesTrait;

    public function __construct(ConnectionInterface $connection, string ...$tables)
    {
        parent::__construct($connection, $tables[0]);

        $this->setTables($tables);
    }
}
