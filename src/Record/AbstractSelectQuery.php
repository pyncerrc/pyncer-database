<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\QueryResult;
use Pyncer\Database\QueryResultInterface;
use Pyncer\Database\Record\AbstractRecordQuery;
use Pyncer\Database\Record\ColumnsTrait;
use Pyncer\Database\Record\GroupByTrait;
use Pyncer\Database\Record\HavingTrait;
use Pyncer\Database\Record\JoinsTrait;
use Pyncer\Database\Record\LimitTrait;
use Pyncer\Database\Record\OrderByTrait;
use Pyncer\Database\Record\SelectQueryInterface;
use Pyncer\Database\Record\WhereTrait;

abstract class AbstractSelectQuery extends AbstractRecordQuery implements
    SelectQueryInterface
{
    use ColumnsTrait;
    use GroupByTrait;
    use HavingTrait;
    use JoinsTrait;
    use LimitTrait;
    use OrderByTrait;
    use WhereTrait;

    protected bool $distinct = false;

    public function distinct(): static
    {
        $this->distinct = true;
        return $this;
    }

    public function numRows(): int
    {
        // We do not care about order when getting num rows
        $orderBy = $this->orderBys;
        $this->orderBys = [null];

        $result = $this->result();

        $this->orderBys = $orderBy;

        return count($result);
    }

    public function row(): ?array
    {
        $limit = $this->limit;
        $this->limit = [1, 0];

        /** @var object */
        $result = $this->execute();

        $this->limit = $limit;

        return $this->getConnection()->fetch($result);
    }

    public function value(): ?string
    {
        $limit = $this->limit;
        $this->limit = [1, 0];

        /** @var object */
        $result = $this->execute();

        $this->limit = $limit;

        $row = $this->getConnection()->fetchIndexed($result);

        return ($row ? $row[0] : null);
    }

    public function result(?array $params = null): QueryResultInterface
    {
        return new QueryResult($this->getConnection(), $this, $params);
    }
}
