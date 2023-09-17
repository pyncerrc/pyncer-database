<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractDateColumnQuery;

class DateColumnQuery extends AbstractDateColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType(): string
    {
        return 'DATE';
    }
}
