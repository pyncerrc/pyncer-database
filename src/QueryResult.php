<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\Exception\ResultException;
use Pyncer\Database\QueryResultInterface;
use Pyncer\Database\Record\SelectQueryInterface;

use function Pyncer\Array\set_recursive;
use function Pyncer\Array\intersect_keys;

class QueryResult implements QueryResultInterface
{
    use ConnectionTrait;

    private SelectQueryInterface $query;
    private ?array $params;
    private string $prefix;

    private ?object $result;
    private ?int $count;
    private int $offset;
    private int $currentCount;
    private int $currentOffset;
    private ?array $currentRow;
    private bool $endOfQuery = false;

    private bool $rewound = false;

    public function __construct(
        ConnectionInterface $connection,
        SelectQueryInterface $query,
        ?array $params = null
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
        if ($this->result !== null) {
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
            $result = $this->query->execute($this->params);

            $this->connection->setPrefix($prefix);

        } else {
            $this->currentOffset = 0;
            $result = $this->query->execute($this->params);
        }

        if (!is_object($result)) {
            throw new ResultException('Query did not produce a result object.');
        }

        $this->result = $result;

        $this->currentCount = $this->connection->numRows($this->result);

        $this->currentRow = $this->connection->fetch($this->result);
        if ($this->currentRow === null) {
            $this->endOfQuery = true;
        }
    }

    #[\ReturnTypeWillChange]
    public function current(): ?array
    {
        return $this->currentRow;
    }

    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->currentOffset;
    }

    public function next(): void
    {
        if ($this->result === null) {
            return;
        }

        $this->rewound = false;

        ++$this->currentOffset;

        if ($this->count !== null) {
            $relativeOffset = (($this->currentOffset - $this->offset) % $this->count);
            if ($relativeOffset === 0) {
                $prefix = $this->connection->getPrefix();
                $this->connection->setPrefix($this->prefix);

                $this->query->limit($this->count, $this->currentOffset);
                $result = $this->query->execute();

                if (!is_object($result)) {
                    throw new ResultException('Query did not produce a result object.');
                }

                $this->result = $result;

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
        if ($this->result === null) {
            $this->rewind();
        }

        return $this->currentCount;
    }

    public function getRow(): ?array
    {
        $count = $this->count;
        $this->count = 1;

        // If not rewound already, then we need to reset its
        // state so its not a count of 1 for first batch
        $reset = !$this->rewound;

        $this->rewind();

        $row = ($this->valid() ? $this->current() : null);

        $this->count = $count;

        if ($reset) {
            $this->rewound = false;
        }

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
