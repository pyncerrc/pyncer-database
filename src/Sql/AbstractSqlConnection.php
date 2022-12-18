<?php
namespace Pyncer\Database\Sql;

use Pyncer\Database\AbstractConnection;
use Pyncer\Database\Driver;
use Pyncer\Database\Exception\ColumnExistsException;
use Pyncer\Database\Exception\ColumnNotFoundException;
use Pyncer\Database\Exception\DatabaseExistsException;
use Pyncer\Database\Exception\DatabaseNotFoundException;
use Pyncer\Database\Exception\TableExistsException;
use Pyncer\Database\Exception\TableNotFoundException;
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
use Pyncer\Database\Sql\Table\CreateTableQuery;
use Pyncer\Database\Sql\Table\AlterTableQuery;
use Pyncer\Database\Table\CreateTableQueryInterface;
use Pyncer\Database\Table\AlterTableQueryInterface;
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

    protected $time;
    protected int $queryExecutionCount = 0;  // The number of queries that have been executed
    protected ?string $lastQuery = null;
    protected $transactionCount = 0; // Track nested transactions

    private string $connectionId;
    private static int $counter = 0;

    public function __construct(Driver $driver)
    {
        parent::__construct($driver);

        $this->connectionId = $driver->getName() . '_' . ++self::$counter;

        // Lets keep time consistant accross requests
        $this->time = $driver->getParam('time', PYNCER_NOW);

        $this->setCharacterSet($driver->getParam('character_set', $this->getDefaultCharacterSet()));
        $this->setCollation($driver->getParam('collation', $this->getDefaultCollation()));
        $this->setEngine($driver->getParam('engine', $this->getDefaultEngine()));

        $this->setNames($this->getCharacterSet(), $this->getCollation());

        $timeZone = $driver->getParam('time_zone');
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

        return $this->execute(sprintf(
            "SET NAMES %s COLLATE %s",
            $characterSet,
            $collation,
        ));
    }

    public function setTimeZone(string $timezone): bool
    {
        return $this->execute('SET time_zone=\'' . $this->escapeString($timezone) . '\'');
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
            $dateTime = '@' . $this->time;
        }

        $dateTime = pyncer_date_time($dateTime, $local);

        if ($dateTime !== null) {
            $dateTime = $dateTime->format($format);
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
            return $this->execute('SAVEPOINT `trans' . $this->transactionCount . '`');
        }

        return $this->execute('START TRANSACTION');
    }

    public function rollback(): bool
    {
        --$this->transactionCount;

        if ($this->transactionCount > 0) {
            return $this->execute('ROLLBACK TO SAVEPOINT `trans' . ($this->transactionCount + 1) . '`');
        }

        return $this->execute('ROLLBACK');
    }

    public function commit(): bool
    {
        --$this->transactionCount;

        if ($this->transactionCount > 0) {
            return $this->execute('RELEASE SAVEPOINT `trans' . ($this->transactionCount + 1) . '`');
        }

        return $this->execute('COMMIT');
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

    public function functions(string $table, $function): FunctionInterface
    {
        $file = dirname(__DIR__) . DS .
            'driver' . DS .
            $this->getDriver() . DS .
            'functions' . DS .
            $function . '.php';

        if (file_exists($file)) {
            $class = '\Pyncer\Database\Driver\\' . $this->getDriver() . '\Functions\\' . $function;
            return new $class($this);
        }

        $file = __DIR__ . DS .
            'functions' . DS .
            $function . '.php';

        if (file_exists($file)) {
            $class = '\Pyncer\Database\Sql\Function\\' . $function;
            return new $class($this, $table);
        }

        throw new InvalidArgumentException('Function is invalid. (' . $function . ')');
    }

    public function hasDatabase(string $database): bool
    {
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

        return $this->execute(
            "DROP DATABASE IF EXISTS " . $this->buildDatabase($database)
        );
    }

    public function createDatabase(string $database): bool
    {
        if ($this->hasDatabase($database)) {
            throw new DatabaseExistsException($database);
        }

        return $this->execute(
            "CREATE DATABASE IF EXISTS " . $this->buildDatabase($database) .
            " CHARACTER SET " . $this->buildScalar($this->getCharacterSet()) .
            " COLLATE " . $this->buildScalar($this->getCollation())
        );
    }

    public function hasTable(string $table): bool
    {
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

        return $this->execute(
            "TRUNCATE TABLE " . $this->buildTable($table)
        );
    }

    public function hasColumn(string $table, string $column): bool
    {
        if (!$this->hasTable($table)) {
            return false;
        }

        $result = $this->execute(
            "SHOW COLUMNS FROM " . $this->buildTable($table) .
            " LIKE " . $this->buildScalar($this->buildColumn($column, true))
        );

        return ($result && count($result) > 0);
    }

    public function dropColumn(string $table, string $column): bool
    {
        if (!$this->hasColumn($table, $column)) {
            throw new ColumnNotFoundException($table, $column);
        }

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

        return $this->alterTable($table)
            ->rename($oldColumnName, $newColumnName)
            ->execute();
    }
}
