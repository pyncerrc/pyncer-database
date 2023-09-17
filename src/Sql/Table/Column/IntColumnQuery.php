<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractIntColumnQuery;
use Pyncer\Database\Table\Column\IntSize;

class IntColumnQuery extends AbstractIntColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType(): string
    {
        return match ($this->getSize()) {
            IntSize::TINY => 'TINYINT',
            IntSize::SMALL => 'SMALLINT',
            IntSize::MEDIUM => 'MEDIUMINT',
            IntSize::LARGE => 'INT',
            IntSize::BIG => 'BIGINT',
        };
    }
}
