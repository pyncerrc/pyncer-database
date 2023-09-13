<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\Record\WhereQueryInterface;
use Stringable;

interface ConditionsInterface extends Stringable
{
    public function getConnection(): ConnectionInterface;
    public function getTable(): string;
    public function getQuery(): RecordQueryInterface;

    public function orOpen(): static;
    public function orClose(): static;

    public function andOpen(): static;
    public function andClose(): static;

    public function notOpen(): static;
    public function notClose(): static;
    public function not(): static;

    public function isInOr(): bool;
    public function isInAnd(): bool;
    public function isInNot(): bool;

    public function compare(mixed $column, mixed $value, string $operator = '=', bool $caseSensitive = false): static;
    public function inList(mixed $column, mixed $value, string $separator = ',', bool $caseSensitive = false): static;
    public function inArray(mixed $column, array $values, bool $caseSensitive = false): static;
    public function startsWith(mixed $column, mixed $value, string $operator = '=', bool $caseSensitive = false): static;
    public function endsWith(mixed $column, mixed $value, string $operator = '=', bool $caseSensitive = false): static;
    public function contains(mixed $column, mixed $value, bool $caseSensitive = false): static;
    public function like(mixed $column, mixed $value, bool $caseSensitive = false): static;
    public function bit(mixed $column, int $mask): static;

    public function dateCompare(mixed $column, mixed $date, string $operator = '='): static;
    public function dateBetween(mixed $column, mixed $startDate, mixed $endDate): static;
    public function dateTimeCompare(mixed $column, mixed $dateTime, string $operator = '='): static;
    public function dateTimeBetween(mixed $column, mixed $startDateTime, mixed $endDateTime): static;
    public function dateTimeYear(mixed $column, ?int $year = null, string $operator = '='): static;
    public function dateTimeMonth(mixed $column, ?int $month = null, string $operator = '='): static;
    public function dateTimeDay(mixed $column, ?int $day = null, string $operator = '='): static;
    public function dateTimeHour(mixed $column, ?int $hour = null, string $operator = '='): static;
    public function dateTimeMinute(mixed $column, ?int $minute = null, string $operator = '='): static;
    public function dateTimeSecond(mixed $column, ?int $second = null, string $operator = '='): static;
    public function yearsAgo(mixed $column, mixed $value, string $operator = '='): static;

    public function columnCompare(mixed $column, mixed $value, string $operator = '=', bool $caseSensitive = false): static;

    public function getQueryString(): string;
}
