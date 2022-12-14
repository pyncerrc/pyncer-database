<?php
namespace Pyncer\Database;

use Pyncer as p;
use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\QueryResultInterface;
use Pyncer\Database\Record\SelectQueryInterface;

use function Pyncer\Array\set_recursive;
use function Pyncer\Array\intersect_keys;

class QueryResult implements QueryResultInterface
{
    use ConnectionTrait;

    private $query;
    private $params;
    private $prefix;

    private $result;
    private $count;
    private $offset;
    private $currentCount;
    private $currentOffset;
    private $currentRow;
    private $endOfQuery = false;

    private $rewound = false;

    public function __construct(
        ConnectionInterface $connection,
        SelectQueryInterface $query,
        array $params = null
    ) {
        $this->setConnection($connection);

        $this->query = $query;
        // Database prefix at time of result creation
        $this->prefix = $this->connection->getPrefix();
        $this->count = $params['count'] ?? null;
        $this->offset = $params['offset'] ?? 0;

        $this->params = $params;
        unset($this->params['count']);
        unset($this->params['offset']);

        $this->result = null;
    }

    public function __destruct()
    {
        if ($this->result) {
            $this->connection->free($this->result);
        }
    }

    public function rewind(): void
    {
        if ($this->rewound) {
            return;
        }

        $this->rewound = true;

        if ($this->count !== null) {
            $this->currentOffset = $this->offset;

            $prefix = $this->connection->getPrefix();
            $this->connection->setPrefix($this->prefix);

            $this->query->limit($this->count, $this->currentOffset);
            $this->result = $this->query->execute($this->params);

            $this->connection->setPrefix($prefix);

        } else {
            $this->currentOffset = 0;
            $this->result = $this->query->execute($this->params);
        }

        $this->currentCount = $this->connection->numRows($this->result);

        $this->currentRow = $this->connection->fetch($this->result);
        if (!$this->currentRow) {
            $this->endOfQuery = true;
        }
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->currentRow;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->currentOffset;
    }

    public function next(): void
    {
        $this->rewound = false;

        ++$this->currentOffset;

        if ($this->count !== null) {
            $relativeOffset = (($this->currentOffset - $this->offset) % $this->count);
            if ($relativeOffset === 0) {
                $prefix = $this->connection->getPrefix();
                $this->connection->setPrefix($this->prefix);

                $this->query->limit($this->count, $this->currentOffset);
                $this->result = $this->query->execute();

                $this->connection->setPrefix($prefix);

                $this->currentCount += $this->connection->numRows($this->result);
            }
        }

        $this->currentRow = $this->connection->fetch($this->result);
        if (!$this->currentRow) {
            $this->endOfQuery = true;
        }
    }

    public function valid(): bool
    {
        return !$this->endOfQuery;
    }

    public function count(): int
    {
        if ($this->currentCount === null) {
            $this->rewind();
        }

        return $this->currentCount;
    }

    public function getRow(): ?array
    {
        $count = $this->count;
        $this->count = 1;

        $this->rewind();

        $row = ($this->valid() ? $this->current() : null);

        $this->count = $count;

        return $row;
    }

    public function getRows(string ...$keys): array
    {
        $data = [];

        if ($keys) {
            foreach ($this as $value) {
                $actualKeys = array_map(function($key) use($value) {
                    return $value[$key] ?? '@';
                }, $keys);
                $data = set_recursive($data, $actualKeys, $value);
            }
        } else {
            foreach ($this as $value) {
                $data[] = $value;
            }
        }

        return $data;
    }

    public function getColumn(string $column, string ...$keys): array
    {
        $data = [];

        if ($keys) {
            foreach ($this as $value) {
                $actualKeys = array_map(function($key) use($value) {
                    return $value[$key] ?? '@';
                }, $keys);
                $data = set_recursive($data, $actualKeys, $value[$column]);
            }
        } else {
            foreach ($this as $value) {
                $data[] = $value[$column];
            }
        }

        return $data;
    }

    public function getColumns(array $columns, string ...$keys): array
    {
        $data = [];

        if ($keys) {
            foreach ($this as $value) {
                $actualKeys = array_map(function($key) use($value) {
                    return $value[$key] ?? '@';
                }, $keys);

                $data = set_recursive(
                    $data,
                    $actualKeys,
                    intersect_keys($value, $columns)
                );
            }
        } else {
            foreach ($this as $value) {
                $data[] = intersect_keys($value, $columns);
            }
        }

        return $data;
    }
}
