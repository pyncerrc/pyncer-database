<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\AbstractRecordsQuery;
use Pyncer\Database\Record\DeleteQueryInterface;
use Pyncer\Database\Record\JoinsTrait;
use Pyncer\Database\Record\WhereTrait;

abstract class AbstractDeleteQuery extends AbstractRecordsQuery implements
    DeleteQueryInterface
{
    use JoinsTrait;
    use WhereTrait;
}
