<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\Table\AbstractTableQuery;
use Pyncer\Database\Table\CreateTableQueryInterface;

abstract class AbstractCreateTableQuery extends AbstractTableQuery implements
    CreateTableQueryInterface
{
    public function execute(array $params = null): bool|array|object
    {
        $table = $this->getTable();

        if ($this->getConnection()->hasTable($table)) {
            throw new TableExistsException($table);
        }

        return parent::execute($params);
    }
}
