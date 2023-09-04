<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\ConnectionTrait;
use Pyncer\Database\Record\ConditionsInterface;
use Pyncer\Database\Record\RecordQueryInterface;
use Pyncer\Database\TableTrait;
use Pyncer\Exception\UnexpectedValueException;

abstract class AbstractConditions implements ConditionsInterface
{
    use ConnectionTrait;
    use TableTrait;

    private RecordQueryInterface $query;
    protected array $conditions = [];
    protected bool $inSingleNot = false;
    protected array $currentBlockType = ['AND'];

    public function __construct(RecordQueryInterface $query)
    {
        $this->setQuery($query);
        $this->setConnection($query->getConnection());
        $this->setTable($query->getTable());
    }

    public function getQuery(): RecordQueryInterface
    {
        return $this->query;
    }
    protected function setQuery(RecordQueryInterface $value): static
    {
        $this->query = $value;
        return $this;
    }

    public function orOpen(): static
    {
        return $this->blockOpen('OR');
    }
    public function orClose(): static
    {
        return $this->blockClose('OR');
    }
    public function andOpen(): static
    {
        return $this->blockOpen('AND');
    }
    public function andClose(): static
    {
        return $this->blockClose('AND');
    }
    public function notOpen(): static
    {
        return $this->blockOpen('NOT');
    }
    public function notClose(): static
    {
        return $this->blockClose('NOT');
    }
    public function not(): static
    {
        $this->notOpen();
        $this->inSingleNot = true;
        return $this;
    }

    public function isInOr(): bool
    {
        return $this->isInBlock('OR');
    }
    public function isInAnd(): bool
    {
        // AND and NOT are AND
        return !$this->isInBlock('OR');
    }
    public function isInNot(): bool
    {
        return $this->isInBlock('NOT');
    }

    private function blockOpen(string $type): static
    {
        if ($this->inSingleNot) {
            throw new UnexpectedValueException('Can\'t open \'' . strtolower($type) . '\' after \'not()\'.');
        }

        $this->conditions[] = ['(', $type];
        array_push($this->currentBlockType, $type);

        return $this;
    }
    private function blockClose(string $type): static
    {
        $lastIndex = (count($this->conditions) - 1);

        if ($this->inSingleNot) {
            throw new UnexpectedValueException('Can\'t close \'' . strtolower($type) . '\' after \'not()\'.');
        }

        if (!$this->isInBlock($type)) {
            throw new UnexpectedValueException('Can\'t close \'' . strtolower($type) . '\' when not last open.');
        }

        if ($this->conditions[$lastIndex][0] === '(' &&
            $this->conditions[$lastIndex][1] === $type
        ) {
            array_pop($this->conditions);
        } else {
            $this->conditions[] = [')', $type];
        }

        array_pop($this->currentBlockType);

        return $this;
    }
    private function isInBlock(string $type): bool
    {
        if (!$this->currentBlockType) {
            return false;
        }

        $index = count($this->currentBlockType) - 1;

        if ($this->inSingleNot) {
            --$index;

            if ($index === -1) {
                return false;
            }
        }

        return ($this->currentBlockType[$index] === $type);
    }

    public function compare(
        mixed $column,
        mixed $value,
        string $operator = '=',
        bool $caseSensitive = false
    ): static
    {
        return $this->addCondition('compare', func_get_args());
    }

    public function inList(
        mixed $column,
        mixed $value,
        string $separator = ',',
        bool $caseSensitive = false
    ): static
    {
        return $this->addCondition('inList', func_get_args());
    }

    public function inArray(
        mixed $column,
        array $values,
        bool $caseSensitive = false
    ): static
    {
        return $this->addCondition('inArray', func_get_args());
    }

    public function startsWith(
        mixed $column,
        mixed $value,
        string $operator = '=',
        bool $caseSensitive = false
    ): static
    {
        return $this->addCondition('startsWith', func_get_args());
    }

    public function endsWith(
        mixed $column,
        mixed $value,
        string $operator = '=',
        bool $caseSensitive = false
    ): static
    {
        return $this->addCondition('endsWith', func_get_args());
    }

    public function contains(
        mixed $column,
        mixed $value,
        bool $caseSensitive = false
    ): static
    {
        return $this->addCondition('contains', func_get_args());
    }

    public function like(
        mixed $column,
        mixed $value,
        bool $caseSensitive = false
    ): static
    {
        return $this->addCondition('like', func_get_args());
    }

    public function bit(mixed $column, int $mask): static
    {
        return $this->addCondition('bit', func_get_args());
    }

    public function dateCompare(
        mixed $column,
        mixed $date,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateCompare', func_get_args());
    }

    public function dateBetween(
        mixed $column,
        mixed $startDate,
        mixed $endDate
    ): static
    {
        return $this->addCondition('dateBetween', func_get_args());
    }

    public function dateTimeCompare(
        mixed $column,
        mixed $dateTime,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateTimeCompare', func_get_args());
    }

    public function dateTimeBetween(
        mixed $column,
        mixed $startDateTime,
        mixed $endDateTime
    ): static
    {
        return $this->addCondition('dateTimeBetween', func_get_args());
    }

    public function dateTimeYear(
        mixed $column,
        ?int $year = null,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateTimeYear', func_get_args());
    }

    public function dateTimeMonth(
        mixed $column,
        ?int $month = null,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateTimeMonth', func_get_args());
    }

    public function dateTimeDay(
        mixed $column,
        ?int $day = null,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateTimeDay', func_get_args());
    }

    public function dateTimeHour(
        mixed $column,
        ?int $hour = null,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateTimeHour', func_get_args());
    }

    public function dateTimeMinute(
        mixed $column,
        ?int $minute = null,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateTimeMinute', func_get_args());
    }

    public function dateTimeSecond(
        mixed $column,
        ?int $second = null,
        string $operator = '='
    ): static
    {
        return $this->addCondition('dateTimeSecond', func_get_args());
    }

    public function yearsAgo(
        mixed $column,
        mixed $value,
        string $operator = '='
    ): static
    {
        return $this->addCondition('yearsAgo', func_get_args());
    }

    private function addCondition(string $type, array $args): static
    {
        $this->conditions[] = [$type, $args];

        if ($this->inSingleNot) {
            $this->inSingleNot = false;
            $this->notClose();
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getQueryString();
    }
}
