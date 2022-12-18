<?php
namespace Pyncer\Database;

interface EncodingInterface
{
    public function getCharacterSet(): string;
    public function setCharacterSet(string $value): static;

    public function getCollation(): string;
    public function setCollation(string $value): static;
}
