<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractStringColumnQuery;

class StringColumnQuery extends AbstractStringColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType(): string
    {
        return 'VARCHAR(' . $this->getLength() . ')';
    }
}
