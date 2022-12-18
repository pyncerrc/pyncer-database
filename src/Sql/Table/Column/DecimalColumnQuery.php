<?php
namespace Pyncer\Database\Sql\Table\Column;

use Pyncer\Database\Sql\Table\Column\ColumnQueryStringTrait;
use Pyncer\Database\Table\Column\AbstractDecimalColumnQuery;

class DecimalColumnQuery extends AbstractDecimalColumnQuery
{
    use ColumnQueryStringTrait;

    public function buildType(): string
    {
        $size = $this->getPrecision() . ',' . $this->getScale();
        return 'DECIMAL(' . $size . ')';
    }
}
