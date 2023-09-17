<?php
namespace Pyncer\Database\Sql\Build;

trait BuildTableTrait
{
    protected function buildTable(?string $table, bool $asValue = false): string
    {
        if ($table === null) {
            return '';
        }

        if (substr($table, 0, 1) === '#') {
            $table = substr($table, 1);
        } else {
            $table = $this->getConnection()->getPrefix() . $table;
        }

        if ($asValue) {
            return $table;
        }

        $table = $this->getConnection()->escapeName($table);

        return "`" . $table . "`";
    }
}
