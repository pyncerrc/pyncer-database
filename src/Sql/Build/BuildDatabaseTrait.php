<?php
namespace Pyncer\Database\Sql\Build;

trait BuildDatabaseTrait
{
    private function buildDatabase(string $database): string
    {
        $database = $this->getConnection()->escapeName($database);

        return "`" . $database . "`";
    }
}
