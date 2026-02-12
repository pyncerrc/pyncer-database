<?php
namespace Pyncer\Database\Sql;

use Countable;
use Pyncer\Database\AbstractConnection;
use Pyncer\Database\Driver;
use Pyncer\Database\Exception\ColumnExistsException;
use Pyncer\Database\Exception\ColumnNotFoundException;
use Pyncer\Database\Exception\DatabaseExistsException;
use Pyncer\Database\Exception\DatabaseNotFoundException;
use Pyncer\Database\Exception\TableExistsException;
use Pyncer\Database\Exception\TableNotFoundException;
use Pyncer\Database\Expression\ExpressionInterface;
use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\DeleteQueryInterface;
use Pyncer\Database\Record\InsertQueryInterface;
use Pyncer\Database\Record\SelectQueryInterface;
use Pyncer\Database\Record\UpdateQueryInterface;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildDatabaseTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Record\DeleteQuery;
use Pyncer\Database\Sql\Record\InsertQuery;
use Pyncer\Database\Sql\Record\SelectQuery;
use Pyncer\Database\Sql\Record\SelectQueryQuery;
use Pyncer\Database\Sql\Record\UpdateQuery;
use Pyncer\Database\Sql\Table\AlterTableQuery;
use Pyncer\Database\Sql\Table\CreateTableQuery;
use Pyncer\Database\Sql\Table\LockTableQuery;
use Pyncer\Database\Table\AlterTableQueryInterface;
use Pyncer\Database\Table\CreateTableQueryInterface;
use Pyncer\Database\Table\LockTableInterface;
use Pyncer\Exception\InvalidArgumentException;
use Pyncer\Exception\RuntimeException;

use function Pyncer\date_time as pyncer_date_time;

use const DIRECTORY_SEPARATOR as DS;
use const Pyncer\NOW as PYNCER_NOW;

abstract class AbstractSqlConnection extends AbstractConnection
{
    use BuildColumnTrait;
    use BuildDatabaseTrait;
    use BuildScalarTrait;
    use BuildTableTrait;

    protected int $time;
    protected int $queryExecutionCount = 0;  // The number of queries that have been executed
    protected ?string $lastQuery = null;
    protected int $transactionCount = 0; // Track nested transactions

    private string $connectionId;
    private static int $counter = 0;

    public function __construct(Driver $driver)
    {
        parent::__construct($driver);

        $this->connectionId = $driver->getName() . '_' . ++self::$counter;

        // Lets keep time consistant accross requests
        $this->time = $driver->getInt('time', PYNCER_NOW);

        $this->setCharacterSet($driver->getString('character_set', $this->getDefaultCharacterSet()));
        $this->setCollation($driver->getString('collation', $this->getDefaultCollation()));
        $this->setEngine($driver->getString('engine', $this->getDefaultEngine()));

        $this->setNames($this->getCharacterSet(), $this->getCollation());

        $timeZone = $driver->getString('time_zone', null);
        if ($timeZone !== null) {
            $this->setTimeZone($timeZone);
        } else { // Default make same as what is set in PHP
            $this->setTimeZone((new \DateTime())->format("P"));
        }
    }

    /**
     * Dummy function for build traits
     */
    private function getConnection(): static
    {
        return $this;
    }

    public function getConnectionId(): string
    {
        return $this->connectionId;
    }

    abstract protected function getDefaultCharacterSet(): string;
    abstract protected function getDefaultCollation(): string;
    abstract protected function getDefaultEngine(): string;

    public function setNames(string $characterSet, string $collation): bool
    {
        $characterSet = $this->buildScalar($this->getCharacterSet());
        $collation = $this->buildScalar($this->getCollation());

        /** @var bool */
        return $this->execute(sprintf(
            "SET NAMES %s COLLATE %s",
            $characterSet,
            $collation,
        ));
    }

    public function setTimeZone(string $timezone): bool
    {
        $timeZone = $this->buildScalar($timezone);

        /** @var bool */
        return $this->execute(
            'SET time_zone=' . $timeZone
        );
    }

    public function date(mixed $dateTime = -1, bool $local = false): string
    {
        return $this->formatDateTime($dateTime, $local, 'Y-m-d');
    }

    public function time(mixed $dateTime = -1, bool $local = false): string
    {
        return $this->formatDateTime($dateTime, $local, 'H:i:s');
    }

    public function dateTime(mixed $dateTime = -1, bool $local = false): string
    {
        return $this->formatDateTime($dateTime, $local, 'Y-m-d H:i:s');
    }

    private function formatDateTime(
        mixed $dateTime,
        bool $local,
        string $format
    ): string
    {
        if ($dateTime === -1) {
            $dateTime = $this->time;
        }

        $dateTime = pyncer_date_time($dateTime, $local);

        if ($dateTime !== null) {
            $dateTime = $dateTime->format($format);
        } else {
            $dateTime = '';
        }

        return $dateTime;
    }

    public function getQueryExecutionCount(): int
    {
        return $this->queryExecutionCount;
    }

    public function getLastQuery(): ?string
    {
        return $this->lastQuery;
    }

    public function start(): bool
    {
        ++$this->transactionCount;

        if ($this->transactionCount > 1) {
            /** @var bool */
            return $this->execute('SAVEPOINT `trans' . $this->transactionCount . '`');
        }

        /** @var bool */
        return $this->execute('START TRANSACTION');
    }

    public function rollback(): bool
    {
        --$this->transactionCount;

        if ($this->transactionCount > 0) {
            /** @var bool */
            return $this->execute('ROLLBACK TO SAVEPOINT `trans' . ($this->transactionCount + 1) . '`');
        }

        /** @var bool */
        return $this->execute('ROLLBACK');
    }

    public function commit(): bool
    {
        --$this->transactionCount;

        if ($this->transactionCount > 0) {
            /** @var bool */
            return $this->execute('RELEASE SAVEPOINT `trans' . ($this->transactionCount + 1) . '`');
        }

        /** @var bool */
        return $this->execute('COMMIT');
    }

    public function autocommit(bool $on): bool
    {
        return $this->execute('SET autocommit=' . ($on ? '1' : '0'));
    }

    public function lock(): LockTableInterface
    {
        return new LockTableQuery($this);
    }
    public function unlock(): bool
    {
        return $this->execute('UNLOCK TABLES');
    }

    public function select(string $table): SelectQueryInterface
    {
        return new SelectQuery($this, $table);
    }

    public function selectQuery(string $table, SelectQueryInterface $query): SelectQueryInterface
    {
        return new SelectQueryQuery($this, $table, $query);
    }

    public function insert(string $table): InsertQueryInterface
    {
        return new InsertQuery($this, $table);
    }

    public function update(string $table): UpdateQueryInterface
    {
        return new UpdateQuery($this, $table);
    }

    public function delete(string $table): DeleteQueryInterface
    {
        return new DeleteQuery($this, $table);
    }

    public function functions(
        string $table,
        string $function
    ): FunctionInterface
    {
        $driverName = $this->getDriver()->getName();

        $file = dirname(__DIR__) . DS .
            'Driver' . DS .
            $driverName . DS .
            'Function' . DS .
            $function . 'Function.php';

        if (file_exists($file)) {
            $class = '\Pyncer\Database\Driver\\' . $driverName .
                '\Function\\' . $function . 'Function';

            /** @var FunctionInterface */
            return new $class($this, $table);
        }

        $file = __DIR__ . DS .
            'Function' . DS .
            $function . 'Function.php';

        if (file_exists($file)) {
            $class = '\Pyncer\Database\Sql\Function\\' . $function . 'Function';

            /** @var FunctionInterface */
            return new $class($this, $table);
        }

        throw new InvalidArgumentException('Function is invalid. (' . $function . ')');
    }

    public function expression(): ExpressionInterface
    {
        $driverName = $this->getDriver()->getName();

        $file = dirname(__DIR__) . DS .
            'Driver' . DS .
            $driverName . DS .
            'Expression' . DS .
            'Expression.php';

        if (file_exists($file)) {
            $class = '\Pyncer\Database\Driver\\' . $driverName .
                '\Expression\\Expression';

            /** @var ExpressionInterface */
            return new $class($this);
        }

        $file = __DIR__ . DS . 'Expression' . DS . 'Expression.php';

        $class = '\Pyncer\Database\Sql\Expression\\Expression';

        /** @var ExpressionInterface */
        return new $class($this);
    }

    public function hasDatabase(string $database): bool
    {
        /** @var object */
        $result = $this->execute(
            "SHOW DATABASES LIKE " . $this->buildScalar($database)
        );

        return ($this->numRows($result) > 0);
    }

    public function dropDatabase(string $database): bool
    {
        if (!$this->hasDatabase($database)) {
            throw new DatabaseNotFoundException($database);
        }

        /** @var bool */
        return $this->execute(
            "DROP DATABASE IF EXISTS " . $this->buildDatabase($database)
        );
    }

    public function createDatabase(string $database): bool
    {
        if ($this->hasDatabase($database)) {
            throw new DatabaseExistsException($database);
        }

        /** @var bool */
        return $this->execute(
            "CREATE DATABASE IF EXISTS " . $this->buildDatabase($database) .
            " CHARACTER SET " . $this->buildScalar($this->getCharacterSet()) .
            " COLLATE " . $this->buildScalar($this->getCollation())
        );
    }

    public function hasTable(string $table): bool
    {
        /** @var object */
        $result = $this->execute(
            "SHOW TABLES LIKE " . $this->buildScalar($this->buildTable($table, true))
        );

        return ($this->numRows($result) > 0);
    }

    public function dropTable(string $table): bool
    {
        if (!$this->hasTable($table)) {
            throw new TableNotFoundException($table);
        }

        /** @var bool */
        return $this->execute(
            "DROP TABLE " . $this->buildTable($table)
        );
    }

    public function createTable(string $table): CreateTableQueryInterface
    {
        if ($this->hasTable($table)) {
            throw new TableExistsException($table);
        }

        return new CreateTableQuery($this, $table);
    }

    public function alterTable(string $table): AlterTableQueryInterface
    {
        if (!$this->hasTable($table)) {
            throw new TableNotFoundException($table);
        }

        return new AlterTableQuery($this, $table);
    }

    public function renameTable(
        string $oldTableName,
        string $newTableName
    ): bool
    {
        if (!$this->hasTable($oldTableName)) {
            throw new TableNotFoundException($oldTableName);
        }

        if ($this->hasTable($newTableName)) {
            throw new TableExistsException($newTableName);
        }

        /** @var bool */
        return $this->execute(
            "RENAME TABLE " . $this->buildTable($oldTableName) .
            " TO " . $this->buildTable($newTableName)
        );
    }

    public function truncateTable(string $table): bool
    {
        if (!$this->hasTable($table)) {
            throw new TableNotFoundException($table);
        }

        /** @var bool */
        return $this->execute(
            "TRUNCATE TABLE " . $this->buildTable($table)
        );
    }

    public function hasColumn(string $table, string $column): bool
    {
        if (!$this->hasTable($table)) {
            return false;
        }

        /** @var Countable */
        $result = $this->execute(
            "SHOW COLUMNS FROM " . $this->buildTable($table) .
            " LIKE " . $this->buildScalar($column)
        );

        return (count($result) > 0);
    }

    public function dropColumn(string $table, string $column): bool
    {
        if (!$this->hasColumn($table, $column)) {
            throw new ColumnNotFoundException($table, $column);
        }

        /** @var bool */
        return $this->execute(
            "ALTER TABLE " . $this->buildTable($table) .
            " DROP " . $this->buildColumn($column)
        );
    }

    public function renameColumn(
        string $table,
        string $oldColumnName,
        string $newColumnName
    ): bool
    {
        if (!$this->hasColumn($table, $oldColumnName)) {
            throw new ColumnNotFoundException($table, $oldColumnName);
        }

        if ($this->hasColumn($table, $newColumnName)) {
            throw new ColumnExistsException($table, $newColumnName);
        }

        /** @var bool */
        return $this->alterTable($table)
            ->rename($oldColumnName, $newColumnName)
            ->execute();
    }
}
