<?php
namespace Pyncer\Database\Record;

use Pyncer\Exception\InvalidArgumentException;

trait LimitTrait
{
    protected ?array $limit = null;

    public function limit(int $count, int $offset = 0): static
    {
        $count = intval($count);
        $offset = intval($offset);

        if ($count < 1) {
            throw new InvalidArgumentException('Count must be greater than 0.');
        } elseif ($offset < 0) {
            throw new InvalidArgumentException('Offset must be greater than or equal to 0.');
        }

        $this->limit = [$count, $offset];

        return $this;
    }
}
