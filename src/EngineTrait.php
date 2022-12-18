<?php
namespace Pyncer\Database;

trait EngineTrait
{
    private string $engine;

    public function getEngine(): string
    {
        return $this->engine;
    }
    public function setEngine(string $value): static
    {
        $this->engine = $value;
        return $this;
    }
}
