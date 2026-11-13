<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Driver;
use Pyncer\Database\EncodingInterface;
use Pyncer\Database\EncodingTrait;
use Pyncer\Database\EngineInterface;
use Pyncer\Database\EngineTrait;
use Pyncer\Database\Exception\ColumnExistsException;
use Pyncer\Database\Exception\ColumnNotFoundException;

abstract class AbstractConnection implements
    ConnectionInterface,
    EncodingInterface,
    EngineInterface
{
    use EncodingTrait;
    use EngineTrait;

    protected string $prefix; // Current prefix to prepend to table names

    public function __construct(protected Driver $driver)
    {
        $this->setPrefix($driver->getPrefix());
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getDriver(): Driver
    {
        return $this->driver;
    }

    public function getDatabase(): string
    {
        return $this->getDriver()->getDatabase();
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
    public function setPrefix(string $value): static
    {
        $this->prefix = $value;
        return $this;
    }

    public function fetchValue(object $result): mixed
    {
        $row = $this->fetchIndexed($result);

        if ($row !== null) {
            return $row[0];
        }

        return $row;
    }

    public function dropColumn(string $table, string $column): bool
    {
        if (!$this->hasColumn($table, $column)) {
            throw new ColumnNotFoundException($table, $column);
        }

        /** @var bool */
        return $this->alterTable($table)
            ->dropColumn($column)
            ->execute();
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
            ->rename($newColumnName, $oldColumnName)
            ->execute();
    }
}
