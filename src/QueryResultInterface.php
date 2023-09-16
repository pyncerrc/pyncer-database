<?php
namespace Pyncer\Database;

use Countable;
use Iterator;

/**
 * @extends Iterator<int, array>
 */
interface QueryResultInterface extends Iterator, Countable
{
    public function getRow(): ?array;
    public function getRows(string ...$keys): array;
    public function getColumn(string $column, string ...$keys): array;
    public function getColumns(array $columns, string ...$keys): array;
}
