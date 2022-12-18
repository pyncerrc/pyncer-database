<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\AbstractTableQuery;
use Pyncer\Database\Record\UpdateQueryInterface;
use Pyncer\Database\Record\ValuesTrait;
use Pyncer\Database\Record\WhereTrait;

abstract class AbstractUpdateQuery extends AbstractRecordQuery implements
    UpdateQueryInterface
{
    use ValuesTrait;
    use WhereTrait;
}
