<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Record\RecordQueryInterface;

interface WhereQueryInterface extends RecordQueryInterface
{
    /**
    * Sets the conditions of the returned rows.
    *
    * @param iterable $conditions A list of comparison conditions
    *
    * @return static
    */
    public function where(iterable $conditions): static;

    /**
    * @return \Pyncer\Database\Record\ConditionsInterface
    */
    public function getWhere(): ConditionsInterface;
    public function setWhere(ConditionsInterface $conditions): static;
}
