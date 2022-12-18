<?php
namespace Pyncer\Database\Record;

use Pyncer\Exception\InvalidArgumentException;

trait JoinsTrait
{
    protected array $joins = [];

    /**
    * @return static
    */
    private function addJoin(string $type, string|array $table, array $on): static
    {
        if (is_string($table)) {
            $table = [$table, null];
        } elseif (is_array($table) && count($table) === 2) {
            $table = array_values($table); // Second value is AS
        } else {
            throw new InvalidArgumentException('Table is invalid.');
        }

        /*
        $query->joinOn(
            $table,
            [
                [[$table, $column], $column2],
                [[$table, $column], [$query->table, $column2]],
                [[$table, $column], [$query->table, $column2], '=,>,<,!=,>=,<='],
                [[$table, $column], [null, $value], '=,>,<,!=,>=,<='],
            ]
        );
        */
        foreach ($on as $key => $comparison) {
            if (!is_array($comparison)) {
                throw new InvalidArgumentException('Join comparison is invalid.');
            }

            if (count($comparison) === 2) {
                $comparison[] = '=';
            }

            if (count($comparison) !== 3 || !in_array($comparison[2], ['=','!=','>','<','>=','<='])) {
                throw new InvalidArgumentException('Join comparison is invalid.');
            }

            if (is_string($comparison[0])) {
                $on[$key][0] = [$table[1] ? $table[1] : $table[0], $comparison[0]];
            } elseif (is_array($comparison[0]) && count($comparison[0]) === 2) {
                $on[$key][0] = array_values($comparison[0]);
            } else {
                throw new InvalidArgumentException('Join comparison column is invalid.');
            }

            if ($comparison[1] === null) {
                $on[$key][1] = [null, null];
            } elseif (is_string($comparison[1])) {
                $on[$key][1] = [$this->getTable(), $comparison[1]];
            } elseif (is_array($comparison[1]) && count($comparison[1]) === 2) {
                $on[$key][1] = array_values($comparison[1]);
            } else {
                throw new InvalidArgumentException('Join comparison column is invalid.');
            }

            $on[$key][2] = $comparison[2];

            // Null compares can only be = or != (IS / IS NOT)
            if ($on[$key][1][0] === null &&
                $on[$key][1][1] === null &&
                !in_array($on[$key][2], ['=', '!='])
            ) {
                throw new InvalidArgumentException('Join comparison is invalid.');
            }
        }

        $join = [
            $type,
            $table,
            $on
        ];

        if ($this->isExistingJoin($join)) {
            throw new InvalidArgumentException('Join conflicts with an existing join.');
        }

        $this->joins[] = $join;

        return $this;
    }

    private function isExistingJoin(array $join): bool
    {
        foreach ($this->joins as $value) {
            if ($value[1][0] === $join[1][0] && $value[1][1] === $join[1][1]) {
                return true;
            }
        }

        return false;
    }

    public function hasJoined(string|array $table): bool
    {
        if (is_string($table)) {
            $table = [$table, null];
        } elseif (is_array($table) && count($table) === 2) {
            $table = array_values($table); // Second value is AS
        } else {
            throw new InvalidArgumentException('Table is invalid.');
        }

        foreach ($this->joins as $value) {
            if ($value[1][0] === $table[0] && $value[1][1] === $table[1]) {
                return true;
            }
        }

        return false;
    }

    /**
    * @return static
    */
    public function join(string|array $table, string $column, string|array $on): static
    {
        $on = [
            [$column, $on]
        ];
        return $this->addJoin('join', $table, $on);
    }
    /**
    * @return static
    */
    public function leftJoin(string|array $table, string $column, string|array $on): static
    {
        $on = [
            [$column, $on]
        ];
        return $this->addJoin('left', $table, $on);
    }
    /**
    * @return static
    */
    public function rightJoin(string|array $table, string $column, string|array $on): static
    {
        $on = [
            [$column, $on]
        ];
        return $this->addJoin('right', $table, $on);
    }

    /**
    * @return static
    */
    public function joinOn(string|array $table, array $on): static
    {
        return $this->addJoin('join', $table, $on);
    }
    /**
    * @return static
    */
    public function leftJoinOn(string|array $table, array $on): static
    {
        return $this->addJoin('left', $table, $on);
    }
    /**
    * @return static
    */
    public function rightJoinOn(string|array $table, array $on): static
    {
        return $this->addJoin('right', $table, $on);
    }
}
