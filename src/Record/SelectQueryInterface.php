<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\QueryResultInterface;
use Pyncer\Database\Record\ColumnsQueryInterface;
use Pyncer\Database\Record\GroupByQueryInterface;
use Pyncer\Database\Record\HavingQueryInterface;
use Pyncer\Database\Record\JoinsQueryInterface;
use Pyncer\Database\Record\LimitQueryInterface;
use Pyncer\Database\Record\OrderByQueryInterface;
use Pyncer\Database\Record\WhereQueryInterface;

interface SelectQueryInterface extends
    ColumnsQueryInterface,
    GroupByQueryInterface,
    HavingQueryInterface,
    JoinsQueryInterface,
    LimitQueryInterface,
    OrderByQueryInterface,
    WhereQueryInterface
{
    public function distinct(): static;

    public function numRows(): int;

    public function row(): ?array;

    public function value(): ?string;

    public function result(array $params = null): QueryResultInterface;
}
