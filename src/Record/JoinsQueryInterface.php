<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\RecordQueryInterface;

interface JoinsQueryInterface extends RecordQueryInterface
{
    public function hasJoined(string|array $table): bool;
    public function join(string|array $table, string $column, string|array $on): static;
    public function leftJoin(string|array $table, string $column, string|array $on): static;
    public function rightJoin(string|array $table, string $column, string|array $on): static;
    public function joinOn(string|array $table, array $on): static;
    public function leftJoinOn(string|array $table, array $on): static;
    public function rightJoinOn(string|array $table, array $on): static;
}
