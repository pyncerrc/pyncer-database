<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Record\RecordQueryInterface;

interface HavingQueryInterface extends RecordQueryInterface
{
    public function having(iterable $conditions): static;

    public function getHaving(): ConditionsInterface;
    public function setHaving(ConditionsInterface $conditions): static;
}
