<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractTextColumnQuery;
use Pyncer\Database\Table\Column\TextSize;

class TextColumnQuery extends AbstractTextColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType()
    {
        return match($this->getSize()) {
            TextSize::TINY => 'TINYTEXT',
            TextSize::SMALL => 'TEXT',
            TextSize::MEDIUM => 'MEDIUMTEXT',
            TextSize::LONG => 'LONGTEXT',
        };
    }
}
