<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\WhereConditionQueryInterface;

interface UpdateQueryInterface extends
    ValuesQueryInterface,
    WhereQueryInterface
{}
