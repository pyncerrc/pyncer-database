<?php
namespace Pyncer\Database\Record;

use Pyncer\Database\Record\ConditionsInterface;

use function in_array;
use function substr;

trait ConditionsQueryTrait
{
    private function setConditions(
        ConditionsInterface $conditions,
        iterable $values
    ): static
    {
        foreach ($values as $column => $value) {
            if (substr($column, 0, 1) === '!') {
                $not = true;
                $column = substr($column, 1);
            } else {
                $not = false;
            }

            if (is_array($value)) {
                if ($not) {
                    $where->not();
                }
                $conditions->inArray(
                    $column,
                    $value,
                    false
                );
            } else {
                $conditions->compare($column, $value, ($not ? '!=' : '='));
            }
        }

        return $this;
    }
}
