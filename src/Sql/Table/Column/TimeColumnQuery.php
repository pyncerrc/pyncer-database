<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractTimeColumnQuery;

class TimeColumnQuery extends AbstractTimeColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType()
    {
        if ($this->getPrecision() > 0) {
            return 'TIME(' . $this->getPrecision() . ')';
        }

        return 'TIME';
    }
}
