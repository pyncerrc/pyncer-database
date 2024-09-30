<?php
namespace Pyncer\Database\Sql\Record;

use Pyncer\Database\Record\AbstractConditions;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildConditionColumnTrait;
use Pyncer\Database\Sql\Build\BuildConditionsTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Exception\UnexpectedValueException;

class Conditions extends AbstractConditions
{
    use BuildTableTrait;
    use BuildColumnTrait;
    use BuildConditionColumnTrait;
    use BuildConditionsTrait;
    use BuildScalarTrait;

    public function getQueryString(): string
    {
        $query = '';

        $blockIndex = 0;
        $blockType = ['AND'];
        $first = true;

        foreach ($this->conditions as $value) {
            if (!$first && $value[0] !== ')') {
                $query .= ' ' . $blockType[$blockIndex] . ' ';
            }

            $first = false;

            if ($value[0] == '(') {
                $first = true;
                ++$blockIndex;
                if ($value[1] === 'NOT') {
                    $blockType[] = 'AND';
                    $query .= ' NOT (';
                } else {
                    $blockType[] = $value[1];
                    $query .= '(';
                }
                continue;
            }

            if ($value[0] == ')') {
                array_pop($blockType);
                --$blockIndex;
                $query .= ')';
                continue;
            }

            $query .= match ($value[0]) {
                'compare' => $this->buildCompareCondition(...$value[1]),
                'inList' => $this->buildInListCondition(...$value[1]),
                'inArray' => $this->buildInArrayCondition(...$value[1]),
                'startsWith' => $this->buildStartsWithCondition(...$value[1]),
                'endsWith' => $this->buildEndsWithCondition(...$value[1]),
                'contains' => $this->buildContainsCondition(...$value[1]),
                'like' => $this->buildLikeCondition(...$value[1]),
                'bit' => $this->buildBitCondition(...$value[1]),
                'dateCompare' => $this->buildDateCompareCondition(...$value[1]),
                'dateBetween' => $this->buildDateBetweenCondition(...$value[1]),
                'dateTimeCompare' => $this->buildDateTimeCompareCondition(...$value[1]),
                'dateTimeBetween' => $this->buildDateTimeBetweenCondition(...$value[1]),
                'dateTimeYear' => $this->buildDateTimePartCondition('YEAR', ...$value[1]),
                'dateTimeMonth' => $this->buildDateTimePartCondition('MONTH', ...$value[1]),
                'dateTimeDay' => $this->buildDateTimePartCondition('DAY', ...$value[1]),
                'dateTimeHour' => $this->buildDateTimePartCondition('HOUR', ...$value[1]),
                'dateTimeMinute' => $this->buildDateTimePartCondition('MINUTE', ...$value[1]),
                'dateTimeSecond' => $this->buildDateTimePartCondition('SECOND', ...$value[1]),
                'yearsAgo' => $this->buildYearsAgoCondition(...$value[1]),
                'matchAgainst' => $this->buildMatchAgainstCondition(...$value[1]),
                'columnCompare' => $this->buildColumnCompareCondition(...$value[1]),
                default => throw new UnexpectedValueException('Unexpected conditon.'),
            };
        }

        return $query;
    }
}
