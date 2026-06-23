<?php
namespace Pyncer\Database\Sql\Function;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\Function\FunctionInterface;
use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Record\ConditionsQueryTrait;
use Pyncer\Database\Sql\Record\Conditions;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\UnexpectedValueException;

class CaseFunction implements FunctionInterface
{
    use ConnectionTrait;
    use TableTrait;
    use ConditionsQueryTrait;
    use BuildScalarTrait;

    //protected ?string $case = null;
    protected ?string $else = null;
    protected array $conditions = [];

    public function __construct(
        ConnectionInterface $connection,
        string $table
    ) {
        $this->setConnection($connection);
        $this->setTable($table);
    }

    public function when(iterable $conditions): static
    {
        $when = $this->getWhen();

        return $this->setConditions($when, $conditions);
    }

    public function getWhen(): ConditionsInterface
    {
        $when = new Conditions($this);

        $this->conditions[] = [$when, null];

        return $when;
    }

    public function then(mixed $result): static
    {
        $index = count($this->conditions) - 1;

        if ($index < 0) {
            throw new UnexpectedValueException('When condition not set.');
        }

        $result = $this->buildScalar($result);

        $this->conditions[$index][1] = $result;

        return $this;
    }

    public function else(mixed $result): static
    {
        $this->else = $this->buildScalar($result);
        return $this;
    }

    public function getQueryString(): string
    {
        if (!$this->conditions) {
            if ($this->else === null) {
                return 'NULL';
            }

            return $this->else;
        }

        $query = 'CASE';

        foreach ($this->conditions as $condition) {
            $query .= ' WHEN ' . $condition[0]->getQueryString() . ' THEN ' . $condition[1];
        }

        if ($this->else !== null) {
            $query .= ' ELSE ' . $this->else;
        }

        $query .= ' END';

        return $query;
    }

    public function __toString(): string
    {
        return $this->getQueryString();
    }
}
