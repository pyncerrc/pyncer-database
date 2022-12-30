<?php
namespace Pyncer\Database;

use Pyncer\Database\Driver;
use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\DeleteQueryInterface;
use Pyncer\Database\Record\InsertQueryInterface;
use Pyncer\Database\Record\SelectQueryInterface;
use Pyncer\Database\Record\UpdateQueryInterface;
use Pyncer\Database\Table\CreateTableQueryInterface;
use Pyncer\Database\Table\AlterTableQueryInterface;

interface ConnectionInterface
{
    public function getDriver(): Driver;

    public function getConnectionId(): string;

    public function connected(): bool;
    public function close(): bool;
    public function error(): array;

    public function getDatabase(): string;

    public function getPrefix(): string;
    public function setPrefix(string $value): static;

    public function date(mixed $dateTime = -1): string;
    public function time(mixed $dateTime = -1): string;
    public function dateTime(mixed $dateTime = -1): string;

    public function getQueryExecutionCount(): int;
    public function getLastQuery(): ?string;

    public function escapeString(string $value): string;
    public function escapeName(string $value): string;

    public function execute(string $query, array $params = null): bool|array|object;

    public function fetch(object $result): ?array;
    public function fetchIndexed(object $result): ?array;
    public function fetchValue(object $result): mixed;

    public function seek(object $result, int $offset): bool;
    public function numRows(object $result): int|string;
    public function free(object $result): bool;
    public function affectedRows(): int|string;

    public function start(): bool;
    public function rollback(): bool;
    public function commit(): bool;

    /**
    * Select rows from the specified table.
    *
    * @param string $table The table to select rows from
    *
    * @return \Pyncer\Database\Query\SelectQueryInterface
    */
    public function select(string $table): SelectQueryInterface;

    /**
    * Select rows from the query
    *
    * @param \Pyncer\Database\Query\SelectQueryInterface $query The query to select rows from
    * @param string $table The temporary table name to give the query
    *
    * @return \Pyncer\Database\Query\SelectQueryInterface
    */
    public function selectQuery(string $table, SelectQueryInterface $query): SelectQueryInterface;

    /**
    * Insert a row into the specified table.
    *
    * @param string $table The table to insert into
    *
    * @return \Pyncer\Database\Query\InsertQueryInterface
    */
    public function insert(string $table): InsertQueryInterface;

    public function insertId(): int;

    /**
    * Updates rows in the specified table.
    *
    * @param string $table The table to update
    *
    * @return \Pyncer\Database\Query\UpdateQueryInterface
    */
    public function update(string $table): UpdateQueryInterface;

    /**
    * Deletes rows in the specified table.
    *
    * @param string $table The table to delete from
    *
    * @return \Pyncer\Database\Query\DeleteQueryInterface
    */
    public function delete(string $table): DeleteQueryInterface;

    public function functions(string $table, string $function): FunctionInterface;

    public function hasDatabase(string $database): bool;
    public function dropDatabase(string $database): bool;
    public function createDatabase(string $database): bool;

    public function hasTable(string $table): bool;
    public function dropTable(string $table): bool;

    /**
    * Add a table to the database.
    *
    * @param string $table The name of the table
    * @return \Pyncer\Database\Query\Table\CreateTableQueryInterface
    */
    public function createTable(string $table): CreateTableQueryInterface;

    /**
    * @return \Pyncer\Database\Query\Table\AlterTableQueryInterface
    */
    public function alterTable(string $table): AlterTableQueryInterface;
    public function renameTable(string $oldTableName, string $newTableName): bool;
    public function truncateTable(string $table): bool;

    public function hasColumn(string $table, string $column): bool;
    public function dropColumn(string $table, string $column): bool;
    public function renameColumn(string $table, string $oldColumnName, string $newColumnName): bool;
}
