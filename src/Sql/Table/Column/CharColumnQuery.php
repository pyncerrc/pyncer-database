<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractCharColumnQuery;

class CharColumnQuery extends AbstractCharColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType(): string
    {
        return 'CHAR(' . $this->getLength() . ')';
    }
}
