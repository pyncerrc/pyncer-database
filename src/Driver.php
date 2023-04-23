<?php
namespace Pyncer\Database;

use Pyncer as p;
use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Exception\DriverNotFoundException;
use Pyncer\Exception\InvalidArgumentException;

use const DIRECTORY_SEPARATOR as DS;

final class Driver
{
    private string $name;
    private string $host;
    private string $database;
    private string $username;
    private string $password;
    private string $prefix;
    private array $params = [];

    public function __construct(
        string $name = '',
        string $host = '',
        string $database = '',
        string $username = '',
        string $password = '',
        string $prefix = '',
        array $params = []
    ) {
        // Set params first so other specific fields take precedence
        $this->setData($params);

        $this->setName($name);
        $this->setHost($host);
        $this->setDatabase($database);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setPrefix($prefix);
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $value): static
    {
        if (!preg_match('/\A[A-Za-z0-9_]+\z/', $value)) {
            throw new InvalidArgumentException(
                'The specified driver name, ' . $value . ', is invalid.'
            );
        }

        $file = __DIR__ . DS . 'Driver' . DS . $value . DS . 'Connection.php';
        if (!file_exists($file)) {
            throw new DriverNotFoundException($value);
        }

        $this->name = $value;

        return $this;
    }

    public function getConnection(): ConnectionInterface
    {
        $class = '\Pyncer\Database\Driver\\' . $this->getName() . '\\Connection';
        return new $class($this);
    }

    public function getHost(): string
    {
        return $this->host;
    }
    public function setHost(string $value): static
    {
        $this->host = $value;
        return $this;
    }
    public function getDatabase(): string
    {
        return $this->database;
    }
    public function setDatabase(string $value): static
    {
        $this->database = $value;
        return $this;
    }
    public function getUsername(): string
    {
        return $this->username;
    }
    public function setUsername(string $value): static
    {
        $this->username = $value;
        return $this;
    }
    public function getPassword(): string
    {
        return $this->password;
    }
    public function setPassword(string $value): static
    {
        $this->password = $value;
        return $this;
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

    public function getParam(string $param, mixed $default = null): mixed
    {
        switch ($param) {
            case "name":
                return $this->getName();
            case "host":
                return $this->getHost();
            case "database":
                return $this->getData();
            case "username":
                return $this->getUsername();
            case "password":
                return $this->getPassword();
            case "prefix":
                return $this->getPrefix();
        }

        return $this->params[$param] ?? $default;
    }
    public function setParam(string $param, mixed $value): static
    {
        switch ($param) {
            case "name":
                return $this->setName($value);
            case "host":
                return $this->setHost($value);
            case "database":
                return $this->setDatabase($value);
            case "username":
                return $this->setUsername($value);
            case "password":
                return $this->setPassword($value);
            case "prefix":
                return $this->setPrefix($value);
        }

        if ($value === null) {
            unset($this->params[$param]);
        } else {
            $this->params[$param] = $value;
        }

        return $this;
    }

    public function getData(): array
    {
        $data = [
            'name' => $this->getName(),
            'host' => $this->getHost(),
            'database' => $this->getDatabase(),
            'username' => $this->getUsername(),
            'password' => $this->getPassword(),
            'prefix' => $this->getPrefix(),
        ];

        foreach ($this->params as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }
    public function setData(array $data): static
    {
        foreach ($data as $key => $value) {
            $this->setParam($key, $value);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
