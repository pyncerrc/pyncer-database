<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\JoinsQueryInterface;
use Pyncer\Database\Record\RecordsQueryInterface;
use Pyncer\Database\Record\WhereQueryInterface;

interface DeleteQueryInterface extends
    RecordsQueryInterface,
    JoinsQueryInterface,
    WhereQueryInterface
{}
