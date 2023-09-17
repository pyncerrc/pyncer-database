<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Record\ConditionsQueryTrait;
use Pyncer\Exception\InvalidArgumentException;

trait HavingTrait
{
    use ConditionsQueryTrait;

    protected ?ConditionsInterface $having = null;

    abstract protected function initializeHaving(): ConditionsInterface;

    /**
    * Sets the having conditions of the returned rows.
    *
    * @param iterable $conditions A list of comparison conditions
    *
    * @return static
    */
    public function having(iterable $conditions): static
    {
        $having = $this->getHaving();

        return $this->setConditions($having, $conditions);
    }

    /**
    * @return \Pyncer\Database\Record\ConditionsInterface
    */
    public function getHaving(): ConditionsInterface
    {
        if ($this->having === null) {
            $this->having = $this->initializeHaving();
        }

        return $this->having;
    }

    public function setHaving(ConditionsInterface $value): static
    {
        $this->having = $value;
        return $this;
    }
}
