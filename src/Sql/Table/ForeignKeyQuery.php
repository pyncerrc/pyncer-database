<?php
namespace Pyncer\Database\Sql\Table;

use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Table\AbstractForeignKeyQuery;
use Pyncer\Database\Table\ReferentialAction;

class ForeignKeyQuery extends AbstractForeignKeyQuery
{
    use BuildColumnTrait;
    use BuildScalarTrait;
    use BuildTableTrait;

    public function getQueryString(): string
    {
        $name = $this->buildColumn($this->getName());

        $table = $this->buildTable($this->getTable());
        $columns = array_map(array($this, 'buildColumn'), $this->getColumns());
        $columns = implode(', ', $columns);

        $refTable = $this->buildTable($this->getReferenceTable());
        $refColumns = array_map(
            array($this, 'buildColumn'),
            $this->getReferenceColumns()
        );
        $refColumns = implode(', ', $refColumns);

        $deleteAction = $this->getDeleteAction();
        $updateAction = $this->getUpdateAction();

        $query = 'ALTER TABLE ' . $table . ' ADD CONSTRAINT ' . $name .
            ' FOREIGN KEY (' . $columns . ')' .
            ' REFERENCES ' . $refTable . ' (' . $refColumns . ')';

        if ($deleteAction !== null) {
            $query .= ' ON DELETE ' . $this->getReferentialAction($deleteAction);
        }

        if ($updateAction !== null) {
            $query .= ' ON UPDATE ' . $this->getReferentialAction($updateAction);
        }

        $query .= ';';

        return $query;
    }

    public function getDefinitionQueryString(): string
    {
        $name = $this->buildColumn($this->getName());

        $columns = array_map(array($this, 'buildColumn'), $this->getColumns());
        $columns = implode(', ', $columns);

        $refTable = $this->buildTable($this->getReferenceTable());
        $refColumns = array_map(
            array($this, 'buildColumn'),
            $this->getReferenceColumns()
        );
        $refColumns = implode(', ', $refColumns);

        $deleteAction = $this->getDeleteAction();
        $updateAction = $this->getUpdateAction();

        $query = 'FOREIGN KEY ' . $name . ' (' . $columns . ')' .
            ' REFERENCES ' . $refTable . ' (' . $refColumns . ')';

        if ($deleteAction !== null) {
            $query .= ' ON DELETE ' . $this->getReferentialAction($deleteAction);
        }

        if ($updateAction !== null) {
            $query .= ' ON UPDATE ' . $this->getReferentialAction($updateAction);
        }

        return $query;
    }

    private function getReferentialAction(ReferentialAction $action): string
    {
        return match($action) {
            ReferentialAction::CASCADE => 'CASCADE',
            ReferentialAction::NO_ACTION => 'NO ACTION',
            ReferentialAction::RESTRICT => 'RESTRICT',
            ReferentialAction::SET_DEFAULT => 'SET DEFAULT',
            ReferentialAction::SET_NULL => 'SET NULL',
        };
    }
}
