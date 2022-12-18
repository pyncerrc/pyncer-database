<?php
namespace Pyncer\Database\Sql\Build;

use Pyncer\Exception\InvalidArgumentException;

use function Pyncer\String\len;

trait BuildConditionsTrait
{
    protected function buildCompareCondition(
        mixed $column,
        mixed $value,
        string $operator = '=',
        bool $caseSensitive = false
    ): string
    {
        $column = $this->buildConditionColumn($column);

        if ($caseSensitive) {
            $column = 'BINARY ' . $column;
        }

        if ($value === null) {
            if ($operator == '=') {
                $operator = 'IS';
            } elseif ($operator == '!=') {
                $operator = 'IS NOT';
            } else {
                throw new InvalidArgumentException('Invalid operator for NULL value.');
            }

            $value = 'NULL';
        } else {
            $value = $this->buildScalar($value);
        }

        return $column . ' ' . $operator . ' ' . $value;
    }

    protected function buildInListCondition(
        mixed $column,
        mixed $value,
        string $separator = ',',
        bool $caseSensitive = false
    ): string
    {
        $column = $this->buildConditionColumn($column);

        if ($caseSensitive) {
            $column = 'BINARY ' . $column;
        }

        $len = len($value) + len($separator);

        $value = $this->database->escapeString($value);

        return '(' . $column . ' = \'' . $value . '\' OR ' . // Equals
            'SUBSTRING(' . $column . ', 1, ' . $len . ') = \'' . $value . $separator . '\' OR ' . // Starts wiith
            'INSTR(' . $column . ', \'' . $separator . $value . $separator . '\') > 0 OR ' . // In middle of list
            'SUBSTRING(' . $column . ', CHAR_LENGTH(' . $column . ') - CHAR_LENGTH(\'' . $separator . $value . '\') + 1, ' . $len . ') = \'' . $separator . $value . '\')';
    }

    protected function buildInArrayCondition(
        mixed $column,
        array $values,
        bool $caseSensitive = false,
    ): string
    {
        // If no items, then fails always or passes always if not is true
        if (!$values) {
            return '1=2';
        }

        // If there is only one item in the array then do a regular compare
        if (count($values) == 1) {
            return $this->buildCompareCondition(
                $column,
                $values[0],
                '=',
                $caseSensitive
            );
        }

        $column = $this->buildConditionColumn($column);
        if ($caseSensitive) {
            $column = 'BINARY ' . $column;
        }

        $hasNull = false;

        foreach ($values as $key => $value) {
            if ($value === null) {
                $hasNull = true;
                unset($values[$key]);
            } else {
                $values[$key] = $this->buildScalar($value);
            }
        }

        return ($hasNull ? '(' : '') .
            $column .
            ' IN (' . implode(',', $values) . ')' .
            ($hasNull ? ' OR ' . $column . ' IS ' . ' NULL)' : '');
    }

    protected function buildStartsWithCondition(
        mixed $column,
        mixed $value,
        string $operator = '=',
        bool $caseSensitive = false
    ): string
    {
        $column = $this->buildConditionColumn($column);

        if ($caseSensitive) {
            $column = 'BINARY ' . $column;
        }

        $len = len($value);
        $value = $this->buildScalar($value);

        return 'SUBSTRING(' . $column . ', 1, ' . $len . ') ' .
            $operator . ' ' . $value;
    }

    protected function buildEndsWithCondition(
        mixed $column,
        mixed $value,
        string $operator = '=',
        bool $caseSensitive = false
    ): string
    {
        $column = $this->buildConditionColumn($column);

        if ($caseSensitive) {
            $column = 'BINARY ' . $column;
        }

        $len = len($value);
        $value = $this->buildScalar($value);

        return 'SUBSTRING(' . $column . ', CHAR_LENGTH(' . $column . ') - CHAR_LENGTH(' . $value . ') + 1, ' . $len . ') ' .
            $operator . ' ' . $value;
    }

    protected function buildContainsCondition(
        mixed $column,
        mixed $value,
        bool $caseSensitive = false,
    ): string
    {
        $column = $this->buildConditionColumn($column);

        $value = $this->escapeWildCards($value);
        $value = $this->database->escapeString($value);
        $value = '\'%' . $value . '%\'';

        if ($caseSensitive) {
            $value = 'BINARY ' . $value;
        }

        return $column . ' ' . 'LIKE ' . $value . ' ESCAPE \'#\'';
    }

    protected function buildLikeCondition(
        mixed $column,
        mixed $value,
        bool $caseSensitive = false,
    ): string
    {
        $column = $this->buildConditionColumn($column);

        $value = $this->escapeWildCards($value);
        $value = str_replace('*', '%', $value);
        $value = $this->database->escapeString($value);
        $value = '\'' . $value . '\'';

        if ($caseSensitive) {
            $value = 'BINARY ' . $value;
        }

        return $column . ' ' . 'LIKE ' . $value . ' ESCAPE \'#\'';
    }

    private function escapeWildCards(string $value): string
    {
        return str_replace(
            ['#', '%', '_'],
            ['##', '#%', '#_'],
            $value
        );
    }

    protected function buildBitCondition(mixed $column, int $mask): static
    {
        $column = $this->buildConditionColumn($column);

        return '(' . $column . ' | ' . $mask . ') = ' . $column;
    }

    protected function buildDateCompareCondition(
        mixed $column,
        $date,
        string $operator = '='
    ): static
    {
        $column = $this->buildConditionColumn($column);

        $date = $this->database->date($date);
        $date = $this->buildScalar($date);

        return 'DATE(' . $column . ') ' . $operator . ' ' . $date;
    }

    protected function buildDateBetweenCondition(
        mixed $column,
        $startDate,
        $endDate
    ): static
    {
        $column = $this->buildConditionColumn($column);

        $startDate = $this->database->date($startDate);
        $startDate = $this->buildScalar($startDate);

        $endDate = $this->database->date($endDate);
        $endDate = $this->buildScalar($endDate);

        return 'DATE(' . $column . ') >= ' . $startDate .
            ' AND DATE(' . $column . ') <= ' . $endDate;
    }

    protected function buildDateTimeCompareCondition(
        mixed $column,
        $dateTime,
        string $operator = '='
    ): static
    {
        $dateTime = $this->database->dateTime($dateTime);
        return $this->buildCompareCondition($column, $dateTime, $operator);
    }

    public function buildDateTimeBetweenCondition(
        mixed $column,
        null|string|DateTimeInterface $startDateTime,
        null|string|DateTimeInterface $endDateTime
    ): static
    {
        $column = $this->buildConditionColumn($column);

        $startDateTime = $this->connection->dateTime($startDateTime);
        $startDateTime = $this->buildScalar($startDateTime);

        $endDateTime = $this->connection->dateTime($endDateTime);
        $endDateTime = $this->buildScalar($endDateTime);

        $this->conditions[] = $column . ' >= ' . $startDateTime .
            ' AND ' . $column . ' <= ' . $endDateTime;

        return $this;
    }

    protected function buildDateTimePartCondition(
        string $part,
        mixed $column,
        ?int $value,
        string $operator
    ): static
    {
        $column = $this->buildConditionColumn($column);

        if ($value === null) {
            $value = $part . '(NOW())';
        } else {
            $value = $this->buildScalar($value);
        }

        return $part . '(' . $column . ') ' . $operator . ' ' . $value;
    }

    public function buildYearsAgoCondition($column, $value, $operator = '=')
    {
        $column = $this->buildConditionColumn($column);

        $age = 'DATE_FORMAT(NOW(), \'%Y\') - DATE_FORMAT(' . $column . ', \'%Y\') - (DATE_FORMAT(NOW(), \'00-%m-%d\') < DATE_FORMAT(' . $column . ', \'00-%m-%d\'))';

        return $age . ' ' . $operator . ' \'' . $this->connection->escapeString($value) . '\'';
    }
}
