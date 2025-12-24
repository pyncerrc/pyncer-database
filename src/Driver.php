<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Exception\DriverNotFoundException;
use Pyncer\Exception\InvalidArgumentException;
use Pyncer\Utility\AbstractDriver;
use Stringable;

final class Driver extends AbstractDriver
{
    public function __construct(
        string $name,
        ?string $host,
        ?string $username,
        ?string $password,
        string $database,
        string $prefix = '',
        array $params = []
    ) {
        parent::__construct($name, $params);

        $this->setHost($host);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setDatabase($database);
        $this->setPrefix($prefix);
    }

    protected function getType(): string
    {
        return 'connection';
    }

    protected function getClass(): string
    {
        return '\Pyncer\Database\Driver\\' . $this->getName() . '\\Connection';
    }

    public function getConnection(): ConnectionInterface
    {
        $class = $this->getClass();

        /** @var ConnectionInterface */
        return new $class($this);
    }

    public function getHost(): ?string
    {
        return $this->getString('host', null);
    }
    public function setHost(?string $value): static
    {
        return $this->setString('host', $value);
    }

    public function getUsername(): ?string
    {
        return $this->getString('username', null);
    }
    public function setUsername(?string $value): static
    {
        return $this->setString('username', $value);
    }

    public function getPassword(): ?string
    {
        return $this->getString('password', null);
    }
    public function setPassword(?string $value): static
    {
        return $this->setString('password', $value);
    }

    public function getDatabase(): string
    {
        /** @var string */
        return $this->getString('database');
    }
    public function setDatabase(string $value): static
    {
        return $this->setString('database', $value);
    }

    public function getPrefix(): string
    {
        /** @var string */
        return $this->getString('prefix');
    }
    public function setPrefix(string $value): static
    {
        return $this->setString('prefix', $value);
    }

    public function set(string $key, mixed $value): static
    {
        switch ($key) {
            case 'host':
            case 'username':
            case 'password':
                if ($value instanceof Stringable) {
                    $value = strval($value);
                }

                if ($value !== null && !is_string($value)) {
                    throw new InvalidArgumentException('The ' . $key . ' param must be a string or null.');
                }
                break;
            case 'database':
            case 'prefix':
                if ($value instanceof Stringable) {
                    $value = strval($value);
                }

                if (!is_string($value)) {
                    throw new InvalidArgumentException('The ' . $key . ' param must be a string.');
                }
                break;
        }

        return parent::set($key, $value);
    }
}
