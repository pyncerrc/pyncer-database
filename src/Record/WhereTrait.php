<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Record\ConditionsQueryTrait;
use Pyncer\Exception\InvalidArgumentException;

trait WhereTrait
{
    use ConditionsQueryTrait;

    protected ?ConditionsInterface $where = null;

    /**
    * Sets the where conditions of the returned rows.
    *
    * @param iterable $conditions A list of comparison conditions
    *
    * @return static
    */
    public function where(iterable $conditions): static
    {
        $where = $this->getWhere();

        return $this->setConditions($where, $conditions);
    }

    /**
    * @return \Pyncer\Database\Record\ConditionsInterface
    */
    public function getWhere(): ConditionsInterface
    {
        if ($this->where === null) {
            $this->where = $this->initializeWhere();
        }

        return $this->where;
    }

    public function setWhere(ConditionsInterface $value): static
    {
        $this->where = $value;
        return $this;
    }
}
