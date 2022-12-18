<?php
namespace Pyncer\Database;

interface EngineInterface
{
    public function getEngine(): string;
    public function setEngine(string $value): static;
}
