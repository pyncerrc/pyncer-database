<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Driver;
use Pyncer\Database\EncodingInterface;
use Pyncer\Database\EncodingTrait;
use Pyncer\Database\EngineInterface;
use Pyncer\Database\EngineTrait;

abstract class AbstractConnection implements
    ConnectionInterface,
    EncodingInterface,
    EngineInterface
{
    use EncodingTrait;
    use EngineTrait;

    protected $prefix; // Current prefix to prepend to table names

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
        $row = $this->fetchIndexed($reuslt);

        if ($row !== null) {
            return $row[0];
        }

        return $row;
    }
}
