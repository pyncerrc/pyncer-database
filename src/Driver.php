<?php
namespace Pyncer\Database;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Exception\DriverNotFoundException;
use Pyncer\Exception\InvalidArgumentException;
use Pyncer\Utility\Params;
use Stringable;

use const DIRECTORY_SEPARATOR as DS;

final class Driver extends Params
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
        // Set params first so other specific fields take precedence
        $this->setData($params);

        $this->setName($name);
        $this->setDatabase($database);
        $this->setHost($host);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setPrefix($prefix);
    }

    public function getName(): string
    {
        /** @var string **/
        return $this->getString('name');
    }
    public function setName(string $value): static
    {
        return $this->setString('name', $value);
    }

    public function getDatabase(): string
    {
        /** @var string **/
        return $this->getString('database');
    }
    public function setDatabase(string $value): static
    {
        return $this->setString('database', $value);
    }

    public function getConnection(): ConnectionInterface
    {
        $class = '\Pyncer\Database\Driver\\' . $this->getName() . '\\Connection';

        /** @var ConnectionInterface **/
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

    public function getPrefix(): string
    {
        /** @var string **/
        return $this->getString('prefix');
    }
    public function setPrefix(string $value): static
    {
        return $this->setString('prefix', $value);
    }

    public function set(string $key, mixed $value): static
    {
        switch ($key) {
            case "name":
                if ($value instanceof Stringable) {
                    $value = strval($value);
                }

                if (!is_string($value)) {
                    throw new InvalidArgumentException('The ' . $key . ' param must be a string.');
                }

                if (!preg_match('/\A[A-Za-z0-9_]+\z/', $value)) {
                    throw new InvalidArgumentException(
                        'The specified driver name, ' . $value . ', is invalid.'
                    );
                }

                $file = __DIR__ . DS . 'Driver' . DS . $value . DS . 'Connection.php';
                if (!file_exists($file)) {
                    throw new DriverNotFoundException($value);
                }
                break;
            case "host":
            case "database":
            case "username":
            case "password":
            case "prefix":
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

    public function __toString(): string
    {
        return $this->getName();
    }
}
