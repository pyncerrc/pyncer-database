<?php
namespace Pyncer\Database\Sql\Build;

trait BuildJoinsTrait
{
    protected function buildJoins(array $joins): string
    {
        $query = '';

        foreach ($joins as $value) {
            switch ($value[0]) {
                case 'join':
                    $query .= " JOIN";
                    break;
                case 'left':
                    $query .= " LEFT JOIN";
                    break;
                case 'right':
                    $query .= " RIGHT JOIN";
                    break;
            }

            $query .= " " . $this->buildTable($value[1][0]);

            if ($value[1][1] !== null) {
                $query .= " AS " .  $this->buildTable($value[1][1]);
            }

            foreach ($value[2] as $key => $on) {
                if ($key == 0) {
                    $query .= " ON ";
                } else {
                    $query .= " AND ";
                }

                $query .= $this->buildTable($on[0][0]) .
                    '.' .
                    $this->buildColumn($on[0][1]);

                if ($on[1][0] === null) { // Comparison value
                    if ($on[1][1] === null) {
                        if ($on[2] === '=') {
                            $query .= ' IS NULL';
                        } else {
                            $query .= ' IS NOT NULL';
                        }
                    } else {
                        $query .= $on[2];
                        $query .= $this->buildScalar($on[1][1]);
                    }
                } else {
                    $query .= $on[2];
                    $query .= $this->buildTable($on[1][0]) .
                        '.' .
                        $this->buildColumn($on[1][1]);
                }
            }
        }

        return ltrim($query);
    }
}
