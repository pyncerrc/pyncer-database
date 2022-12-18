<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractDateTimeColumnQuery;

class DateTimeColumnQuery extends AbstractDateTimeColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType()
    {
        if ($this->getPrecision() > 0) {
            return 'DATETIME(' . $this->getPrecision() . ')';
        }

        return 'DATETIME';
    }
}
