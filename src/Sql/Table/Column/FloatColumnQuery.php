<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractFloatColumnQuery;
use Pyncer\Database\Table\Column\FloatSize;

class FloatColumnQuery extends AbstractFloatColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType()
    {
        return match ($this->getSize()) {
            FloatSize::SINGLE => 'FLOAT',
            FloatSize::DOUBLE => 'DOUBLE',
        };
    }
}
