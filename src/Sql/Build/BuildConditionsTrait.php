<?php
namespace Pyncer\Database\Sql\Build;

use DateTimeInterface;
use Pyncer\Database\Record\SearchMode;
use Pyncer\Exception\InvalidArgumentException;

use function Pyncer\stringify as pyncer_stringify;
use function Pyncer\String\len as pyncer_str_len;

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

        $value = $this->getStringValue($value);

        // Will never be true.
        if ($value === '') {
            return '1=2';
        }

        $len = pyncer_str_len($value) + pyncer_str_len($separator);

        $value = $this->getConnection()->escapeString($value);

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

        $value = $this->getStringValue($value);

        // Will always be true.
        if ($value === '') {
            return '1=1';
        }

        $len = pyncer_str_len($value);
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

        $value = $this->getStringValue($value);

        // Will always be true.
        if ($value === '') {
            return '1=1';
        }

        $len = pyncer_str_len($value);
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

        $value = $this->getStringValue($value);

        // Will always be true.
        if ($value === '') {
            return '1=1';
        }

        $value = $this->escapeWildCards($value);
        $value = $this->getConnection()->escapeString($value);
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

        $value = $this->getStringValue($value);

        // Will never be true.
        if ($value === '') {
            return '1=2';
        }

        $value = $this->escapeWildCards($value);
        $value = str_replace('*', '%', $value);
        $value = $this->getConnection()->escapeString($value);
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

    protected function buildBitCondition(mixed $column, int $mask): string
    {
        $column = $this->buildConditionColumn($column);

        return '(' . $column . ' | ' . $mask . ') = ' . $column;
    }

    protected function buildDateCompareCondition(
        mixed $column,
        mixed $date,
        string $operator = '='
    ): string
    {
        $column = $this->buildConditionColumn($column);

        $date = $this->getConnection()->date($date);
        $date = $this->buildScalar($date);

        return 'DATE(' . $column . ') ' . $operator . ' ' . $date;
    }

    protected function buildDateBetweenCondition(
        mixed $column,
        mixed $startDate,
        mixed $endDate
    ): string
    {
        $column = $this->buildConditionColumn($column);

        $startDate = $this->getConnection()->date($startDate);
        $startDate = $this->buildScalar($startDate);

        $endDate = $this->getConnection()->date($endDate);
        $endDate = $this->buildScalar($endDate);

        return 'DATE(' . $column . ') >= ' . $startDate .
            ' AND DATE(' . $column . ') <= ' . $endDate;
    }

    protected function buildDateTimeCompareCondition(
        mixed $column,
        mixed $dateTime,
        string $operator = '='
    ): string
    {
        $dateTime = $this->getConnection()->dateTime($dateTime);
        return $this->buildCompareCondition($column, $dateTime, $operator);
    }

    public function buildDateTimeBetweenCondition(
        mixed $column,
        mixed $startDateTime,
        mixed $endDateTime
    ): string
    {
        $column = $this->buildConditionColumn($column);

        $startDateTime = $this->getConnection()->dateTime($startDateTime);
        $startDateTime = $this->buildScalar($startDateTime);

        $endDateTime = $this->getConnection()->dateTime($endDateTime);
        $endDateTime = $this->buildScalar($endDateTime);

        return $column . ' >= ' . $startDateTime .
            ' AND ' . $column . ' <= ' . $endDateTime;
    }

    protected function buildDateTimePartCondition(
        string $part,
        mixed $column,
        ?int $value,
        string $operator
    ): string
    {
        $column = $this->buildConditionColumn($column);

        if ($value === null) {
            $value = $part . '(NOW())';
        } else {
            $value = $this->buildScalar($value);
        }

        return $part . '(' . $column . ') ' . $operator . ' ' . $value;
    }

    public function buildYearsAgoCondition(
        mixed $column,
        mixed $value,
        string $operator = '='
    ): string
    {
        $column = $this->buildConditionColumn($column);

        $age = 'DATE_FORMAT(NOW(), \'%Y\') - ' .
            'DATE_FORMAT(' . $column . ', \'%Y\') - ' .
            '(DATE_FORMAT(NOW(), \'00-%m-%d\') < ' .
            'DATE_FORMAT(' . $column . ', \'00-%m-%d\'))';

        $value = $this->buildScalar($value);

        return $age . ' ' . $operator . ' ' . $value;
    }

    public function buildMatchAgainstCondition(
        mixed $column,
        mixed $value,
        SearchMode $searchMode = SearchMode::NATURAL_LANGUAGE
    ): string
    {
        // Support for multiple columns
        if (is_array($column) && is_array($column[0])) {
            $columns = [];

            foreach ($column as $columnValue) {
                $columns[] = $this->buildConditionColumn($columnValue);
            }

            $columns = implode(',', $columns);
        } else {
            $columns = $this->buildConditionColumn($column);
        }

        if ($value === null) {
            $value = '';
        } else {
            $value = $this->buildScalar($value);
        }

        $mode = match ($searchMode) {
            SearchMode::BOOLEAN => 'IN BOOLEAN MODE',
            SearchMode::NATURAL_LANGUAGE => 'IN NATURAL LANGUAGE MODE',
            SearchMode::QUERY_EXPANSION => 'WITH QUERY EXPANSION',
            SearchMode::NATURAL_LANGUAGE_WITH_QUERY_EXPANSION => 'IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION',
        };

        return 'MATCH(' . $columns . ') AGAINST(' . $value . ' ' . $mode . ')';
    }

    protected function buildColumnCompareCondition(
        mixed $column1,
        mixed $column2,
        string $operator = '=',
        bool $caseSensitive = false
    ): string
    {
        $column1 = $this->buildConditionColumn($column1);
        if ($caseSensitive) {
            $column1 = 'BINARY ' . $column1;
        }

        $column2 = $this->buildConditionColumn($column2);

        return $column1 . ' ' . $operator . ' ' . $column2;
    }

    private function getStringValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            $value = $this->getConnection()->dateTime($value);
        } else {
            $value = pyncer_stringify($value) ?? '';
        }

        return $value;
    }
}
