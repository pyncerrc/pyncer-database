<?php
namespace Pyncer\Database\Table;

use Pyncer\Database\EncodingInterface;
use Pyncer\Database\EngineInterface;
use Pyncer\Database\QueryInterface;
use Pyncer\Database\Table\Column\ColumnQueryInterface;
use Pyncer\Database\Table\Column\IntSize;
use Pyncer\Database\Table\Column\FloatSize;
use Pyncer\Database\Table\Column\TextSize;
use Pyncer\Database\Table\CommentInterface;
use Pyncer\Database\Table\ForeignKeyQueryInterface;
use Pyncer\Database\Table\IndexQueryInterface;
use Pyncer\Database\Table\ReferentialAction;

interface TableQueryInterface extends
    CommentInterface,
    EncodingInterface,
    EngineInterface,
    QueryInterface
{
    public function getTable(): string;

    /**
    * Adds an integer column to the table that is set to be the primary key
    * and auto increment.
    *
    * @param string $columnName The name of the column.
    * @param int $size The size of the integer.
    */
    public function serial(string $columnName): static;

    /**
    * Adds an integer column to the table.
    *
    * @param string $columnName The name of the column.
    * @param int $size The size of the integer.
    */
    public function int(string $columnName, IntSize $size = IntSize::LARGE): static;

    /**
    * Adds a double column to the table.
    *
    * @param string $columnName The name of the column.
    */
    public function float(string $columnName, FloatSize $size = FloatSize::DOUBLE): static;

    /**
    * Adds a decimal column to the table.
    *
    * @param string $columnName The name of the column.
    */
    public function decimal(
        string $columnName,
        int $precision = 10,
        int $scale = 0
    ): static;

    /**
    * Adds a char column to the table.
    *
    * @param string $columnName The name of the column.
    * @param int $length The length in characters of the column.
    */
    public function char(string $columnName, int $length): static;

    /**
    * Adds a string column to the table.
    *
    * @param string $columnName The name of the column.
    * @param int $length The max length in characters of the column.
    */
    public function string(string $columnName, int $length = 250): static;

    /**
    * Adds a text column to the table.
    *
    * @param mixed $columnName The name of the column.
    * @param int $size The size of the text.
    */
    public function text(string $columnName, TextSize $size = TextSize::SMALL): static;

    /**
    * Adds a date column to the table.
    *
    * @param string $columnName The name of the column.
    */
    public function date(string $columnName): static;

    /**
    * Adds a time column to the table.
    *
    * @param string $columnName The name of the column.
    */
    public function time(string $columnName, int $precision = 0): static;

    /**
    * Adds a datetime column to the table.
    *
    * @param string $columnName The name of the column.
    */
    public function dateTime(string $columnName, int $precision = 0): static;

    /**
    * Adds a boolean column to the table.
    *
    * @param string $columnName The name of the column.
    */
    public function bool(string $columnName): static;

    /**
    * Adds an enum column to the table.
    *
    * @param string $columnName The name of the column.
    * @param array $values The enum values.
    */
    public function enum(string $columnName, array $values): static;

    public function primary(string ...$columnNames): static;

    public function autoIncrement(string ...$columnNames): static;

    public function unsigned(string ...$columnNames): static;

    public function autoUpdate(string ...$columnNames): static;

    public function null(string ...$columnNames): static;

    public function default(string $value, string ...$columnNames): static;

    public function comment(string $value, string ...$columnNames): static;

    public function index(
        ?string $indexName = null,
        string ...$columnNames,
    ): static;

    public function unique(string ...$indexNames): static;

    public function fulltext(string ...$indexNames): static;

    public function foreignKey(
        ?string $foreignKeyName = null,
        string ...$columnNames,
    ): static;

    public function references(string $table, string ...$columnNames): static;

    public function deleteAction(
        ReferentialAction $action,
        string ...$foreignKeyNames,
    ): static;

    public function updateAction(
        ReferentialAction $action,
        string ...$foreignKeyNames,
    ): static;

    public function engine(string $value): static;

    public function characterSet(string $value, string ...$columnNames): static;
    public function collation(string $value, string ...$columnNames): static;

    public function hasColumn(string $columnName): bool;
    public function dropColumn(string $columnName): static;
    public function getColumn(string $columnName): ColumnQueryInterface;

    public function hasPrimary(): bool;
    public function dropPrimary(): static;
    public function getPrimary(): ?array;

    public function hasIndex(string $indexName): bool;
    public function dropIndex(string $indexName): static;
    public function getIndex(string $indexName): IndexQueryInterface;

    public function hasForeignKey(string $foreignKeyName): bool;
    public function dropForeignKey(string $foreignKeyName): static;
    public function getForeignKey(
        string $foreignKeyName
    ): ForeignKeyQueryInterface;
}
