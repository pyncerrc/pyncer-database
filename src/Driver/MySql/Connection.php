<?php
namespace Pyncer\Database\Driver\MySQL;

use Pyncer\Database\Driver;
use Pyncer\Database\Exception\DatabaseException;
use Pyncer\Database\Exception\QueryException;
use Pyncer\Database\Exception\ResultException;
use Pyncer\Database\Sql\AbstractSqlConnection;
use Pyncer\Exception\InvalidArgumentException;
use Pyncer\Exception\UnexpectedValueException;
use mysqli_result;
use mysqli_sql_exception;

use function mysqli_report;
use function Pyncer\Array\unset_null;
use function Pyncer\Array\ensure_keys;

use const MYSQLI_REPORT_ERROR;
use const MYSQLI_REPORT_STRICT;

class Connection extends AbstractSqlConnection
{
    protected $mysql;

    public function __construct(Driver $driver)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->mysql = mysqli_init();
        if (!$this->mysql) {
            throw new DatabaseException('MySql could not be initialized.');
        }

        $options = $driver->getParam('options');
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $this->mysql->options($key, $value);
            }
        } elseif ($options !== null) {
            throw new UnexpectedValueException('Options param is invalid.');
        }

        $ssl = $driver->getParam('ssl');
        if (is_array($ssl) && unset_null($ssl)) {
            $ssl = ensure_keys(
                $ssl,
                ['key', 'cert', 'ca', 'capath', 'cipher'],
                null
            );

            $this->mysql->ssl_set(
                $ssl['key'],
                $ssl['cert'],
                $ssl['ca'],
                $ssl['capath'],
                $ssl['cipher']
            );
        }

        try {
            $host = $driver->getHost();
            if ($host !== null) {
                if (strpos($host, ':') !== false) {
                    list($host, $port) = explode(':', $host, 2);
                } else {
                    $port = null;
                }

                $connected = $this->mysql->real_connect(
                    $host,
                    $driver->getUsername(),
                    $driver->getPassword(),
                    $driver->getDatabase(),
                    $port,
                    $driver->getParam('socket'),
                    $driver->getParam('flags', 0)
                );
            } else {
                $connected = $this->mysql->real_connect($this->mysql);
            }
        } catch (mysqli_sql_exception $e) {
            $this->mysql->close();
            unset($this->mysql);

            throw new DatabaseException('MySql could not connect to database.', 0, $e);
        }

        $sqlMode = $driver->getParam('sql_mode');
        if (is_array($sqlMode)) {
            $sqlMode = implode(',', $sqlMode);
        } else {
            $sqlMode = strval($sqlMode);
        }
        $sqlMode = trim($sqlMode);

        if ($sqlMode !== '') {
            $this->execute('SET SESSION sql_mode = \'' . $this->escapeString($sqlMode) . '\'');
        }

        parent::__construct($driver);
    }

    protected function getDefaultCharacterSet(): string
    {
        return 'utf8mb4';
    }
    protected function getDefaultCollation(): string
    {
        return 'utf8mb4_unicode_ci';
    }
    protected function getDefaultEngine(): string
    {
        return 'InnoDB';
    }

    public function connected(): bool
    {
        return ($this->mysql ? true : false);
    }
    public function close(): bool
    {
        if ($this->mysql) {
            return $this->mysql->close();
        }

        return false;
    }
    public function error(): array
    {
        return [
            'code' => $this->mysql->errno,
            'message' => $this->mysql->error
        ];
    }

    public function escapeString(string $value): string
    {
        return $this->mysql->real_escape_string($value);
    }
    public function escapeName(string $value): string
    {
        return str_replace('`', '``', $value);
    }

    public function execute(string $query, ?array $params = null): bool|array|object
    {
        if (strlen($query) > 50000) {
            throw new DatabaseException('Max query length of 50,000 reached.');
        }

        $this->lastQuery = $query;
        ++$this->queryExecutionCount;

        $multi = $params['multi'] ?? false;

        try {
            if ($multi) {
                $results = [];

                $result = $this->mysql->multi_query($query);
                if ($this->mysql->field_count) {
                    $result = $this->mysql->store_result();
                }
                $results[] = $result;

                while ($this->mysql->more_results()) {
                    $result = $this->mysql->next_result();
                    if ($this->mysql->field_count) {
                        $result = $this->mysql->store_result();
                    }
                    $results[] = $result;
                }

                $result = $results;
            } else {
                $unbuffered = $params['unbuffered'] ?? false;

                $result = $this->mysql->query(
                    $query,
                    ($unbuffered ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT)
                );
            }
        } catch (mysqli_sql_exception $e) {
            throw new QueryException(
                $query,
                $this->mysql->error,
                $this->mysql->errno,
                $e
            );
        }

        return $result;
    }

    public function fetch(object $result): ?array
    {
        if (!$result instanceof mysqli_result) {
            throw new InvalidArgumentException('Expected mysqli_result.');
        }

        $row = $result->fetch_assoc();

        if ($row === false) {
            throw new DatabaseException('Fetch assoc failed.');
        }

        return $row;
    }
    public function fetchIndexed(object $result): ?array
    {
        if (!$result instanceof mysqli_result) {
            throw new InvalidArgumentException('Expected mysqli_result.');
        }

        $row = $result->fetch_row();

        if ($row === false) {
            throw new DatabaseException('Fetch row failed.');
        }

        return $row;
    }

    public function seek($result, int $offset): bool
    {
        if (!$result instanceof mysqli_result) {
            throw new InvalidArgumentException('Expected mysqli_result.');
        }

        return $result->data_seek($offset);
    }
    public function numRows($result): int|string
    {
        if (!$result instanceof mysqli_result) {
            throw new InvalidArgumentException('Expected mysqli_result.');
        }

        return $result->num_rows;
    }
    public function free($result): bool
    {
        if (!$result instanceof mysqli_result) {
            throw new InvalidArgumentException('Expected mysqli_result.');
        }

        $result->free();
        return true;
    }
    public function affectedRows(): int|string
    {
        if (!$this->mysql) {
            throw new UnexpectedValueException('MySql not initialized.');
        }

        $result = $this->mysql->affected_rows;

        if ($result < 0) {
            throw new ResultException('Affected rows could not be determined.');
        }

        return $result;
    }
    public function insertId(): int
    {
        if (!$this->mysql) {
            throw new UnexpectedValueException('MySql not initialized.');
        }

        $result = $this->mysql->insert_id;

        if ($result === 0) {
            throw new ResultException('No last insert id.');
        }

        return $result;
    }
}
