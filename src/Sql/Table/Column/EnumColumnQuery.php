<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractEnumColumnQuery;

class EnumColumnQuery extends AbstractEnumColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType()
    {
        $values = array_map([$this->getConnection(), 'escapeString'], $this->getValues());
        $values = "'" . implode("','", $values) . "'";
        return 'ENUM(' . $values . ')';
    }
}
