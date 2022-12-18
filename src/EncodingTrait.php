<?php
namespace Pyncer\Database;

trait EncodingTrait
{
    private string $characterSet;
    private string $collation;

    public function getCharacterSet(): string
    {
        return $this->characterSet;
    }
    public function setCharacterSet(string $value): static
    {
        $this->characterSet = $value;
        return $this;
    }

    public function getCollation(): string
    {
        return $this->collation;
    }
    public function setCollation(string $value): static
    {
        $this->collation = $value;
        return $this;
    }
}
