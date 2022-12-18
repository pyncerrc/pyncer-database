<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractBoolColumnQuery;

class BoolColumnQuery extends AbstractBoolColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType(): string
    {
        return "ENUM('0','1')";
    }
}
