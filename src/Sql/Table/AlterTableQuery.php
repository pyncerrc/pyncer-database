<?php
namespace Pyncer\Database\Sql\Table;

use Pyncer\Database\ConnectionInterface;
use Pyncer\Database\EncodingInterface;
use Pyncer\Database\Exception\DatabaseException;
use Pyncer\Database\Sql\Build\BuildColumnTrait;
use Pyncer\Database\Sql\Build\BuildScalarTrait;
use Pyncer\Database\Sql\Build\BuildTableTrait;
use Pyncer\Database\Sql\Table\Column\BoolColumnQuery;
use Pyncer\Database\Sql\Table\Column\CharColumnQuery;
use Pyncer\Database\Sql\Table\Column\DateColumnQuery;
use Pyncer\Database\Sql\Table\Column\DateTimeColumnQuery;
use Pyncer\Database\Sql\Table\Column\DecimalColumnQuery;
use Pyncer\Database\Sql\Table\Column\EnumColumnQuery;
use Pyncer\Database\Sql\Table\Column\FloatColumnQuery;
use Pyncer\Database\Sql\Table\Column\IntColumnQuery;
use Pyncer\Database\Sql\Table\Column\StringColumnQuery;
use Pyncer\Database\Sql\Table\Column\TextColumnQuery;
use Pyncer\Database\Sql\Table\Column\TimeColumnQuery;
use Pyncer\Database\Sql\Table\TableQueryTrait;
use Pyncer\Database\Table\AbstractAlterTableQuery;
use Pyncer\Database\Table\Column\BoolColumnQueryInterface;
use Pyncer\Database\Table\Column\DateTimeColumnQueryInterface;
use Pyncer\Database\Table\Column\IntColumnQueryInterface;
use Pyncer\Database\Table\Column\FloatColumnQueryInterface;
use Pyncer\Database\Table\Column\TimeColumnQueryInterface;
use Pyncer\Database\Table\Column\IntSize;
use Pyncer\Database\Table\Column\FloatSize;
use Pyncer\Database\Table\Column\TextSize;
use Pyncer\Database\Table\ReferentialAction;
use Pyncer\Database\Value;

use function explode;
use function floatval;
use function intval;
use function ltrim;
use function str_contains;
use function str_replace;
use function substr;

class AlterTableQuery extends AbstractAlterTableQuery
{
    use BuildColumnTrait;
    use BuildScalarTrait;
    use BuildTableTrait;
    use TableQueryTrait;

    private array $existingTableInformation;

    public function __construct(ConnectionInterface $connection, string $table)
    {
        parent::__construct($connection, $table);

        /** @var object */
        $result = $connection->execute(sprintf(
            "SELECT
                `ENGINE`,
                `TABLE_COLLATION`,
                `TABLE_COMMENT`
            FROM
                `INFORMATION_SCHEMA`.`TABLES`
            WHERE
                TABLE_SCHEMA = %s AND TABLE_NAME = %s
            ",

            $this->buildScalar($connection->getDatabase()),
            $this->buildScalar($this->buildTable($table, true)),
        ));

        $this->existingTableInformation = $connection->fetch($result) ?? [];
    }

    protected function initializeExistingComment(): ?string
    {
        $comment = $this->existingTableInformation['TABLE_COMMENT'] ?? null;

        if ($comment === '') {
            return null;
        }

        return $comment;
    }

    protected function initializeExistingEngine(): string
    {
        return $this->existingTableInformation['ENGINE'] ??
            $this->getConnection()->getEngine();
    }
    protected function initializeExistingCharacterSet(): string
    {
        $characterSet = $this->existingTableInformation['TABLE_COLLATION'] ?? null;

        if ($characterSet === null) {
            return $this->getConnection()->getCharacterSet();
        }

        $characterSet = explode('_', $characterSet);

        return $characterSet[0];
    }
    protected function initializeExistingCollation(): string
    {
        return $this->existingTableInformation['TABLE_COLLATION'] ??
            $this->getConnection()->getCollation();
    }

    protected function initializeExistingColumns(): array
    {
        $existing = [];

        /** @var object */
        $result = $this->getConnection()->execute(sprintf(
            "SELECT *
            FROM
                `INFORMATION_SCHEMA`.`COLUMNS`
            WHERE
                `TABLE_SCHEMA` = %s AND
                `TABLE_NAME` = %s
            ORDER BY `ORDINAL_POSITION`",
            $this->buildScalar($this->getConnection()->getDatabase()),
            $this->buildScalar($this->buildTable($this->getTable(), true)),
        ));

        while ($row = $this->getConnection()->fetch($result)) {
            switch ($row['DATA_TYPE']) {
                case 'enum';
                    if ($row['COLUMN_TYPE'] === "enum('0','1')") {
                        $column = BoolColumnQuery::fromTableQuery(
                            $this,
                            $row['COLUMN_NAME'],
                        );
                    } else {
                        $values = explode(
                            "','",
                            substr($row['COLUMN_TYPE'], 6, -2)
                        );

                        $column = EnumColumnQuery::fromTableQuery(
                            $this,
                            $row['COLUMN_NAME'],
                            $values
                        );
                    }
                    break;
                case 'char':
                    $column = CharColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        $row['CHARACTER_MAXIMUM_LENGTH'],
                    );
                    break;
                case 'date':
                    $column = DateColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                    );
                    break;
                case 'datetime':
                    $column = DateTimeColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                    );
                    break;
                case 'decimal':
                    $column = DecimalColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        $row['NUMERIC_PRECISION'],
                        $row['NUMERIC_SCALE'],
                    );
                case 'float':
                    $column = FloatColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        FloatSize::SINGLE,
                    );
                    break;
                case 'double':
                    $column = FloatColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        FloatSize::DOUBLE,
                    );
                    break;
                case 'tinyint':
                    $column = IntColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        IntSize::TINY
                    );
                    break;
                case 'smallint':
                    $column = IntColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        IntSize::SMALL,
                    );
                    break;
                case 'mediumint':
                    $column = IntColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        IntSize::MEDIUM,
                    );
                    break;
                case 'int':
                    $column = IntColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        IntSize::LARGE,
                    );
                    break;
                case 'bigint':
                    $column = IntColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        IntSize::BIG,
                    );
                    break;
                case 'varchar':
                    $column = StringColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        $row['CHARACTER_MAXIMUM_LENGTH'],
                    );
                    break;
                case 'tinytext':
                    $column = TextColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        TextSize::TINY
                    );
                    break;
                case 'text':
                    $column = TextColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        TextSize::SMALL
                    );
                    break;
                case 'mediumtext':
                    $column = TextColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        TextSize::MEDIUM
                    );
                    break;
                case 'longtext':
                    $column = TextColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                        TextSize::LONG
                    );
                    break;
                case 'time':
                    $column = TimeColumnQuery::fromTableQuery(
                        $this,
                        $row['COLUMN_NAME'],
                    );
                    break;
                default:
                    throw new DatabaseException(
                        'Unsupported column type. (' . $row['DATA_TYPE'] . ')'
                    );
            }

            if ($row['IS_NULLABLE'] === 'YES') {
                $column->setNull(true);
            }

            if ($column instanceof EncodingInterface) {
                if ($row['CHARACTER_SET_NAME'] !== null) {
                    $column->setCharacterSet($row['CHARACTER_SET_NAME']);
                }

                if ($row['COLLATION_NAME'] !== null) {
                    $column->setCollation($row['COLLATION_NAME']);
                }
            }

            if ($column instanceof IntColumnQueryInterface) {
                if ($row['EXTRA'] === 'auto_increment') {
                    $column->setAutoIncrement(true);
                }

                if (str_contains($row['COLUMN_TYPE'], 'unsigned')) {
                    $column->setUnsigned(true);
                }
            } elseif ($column instanceof DateTimeColumnQueryInterface) {
                if ($row['EXTRA'] === 'on update current_timestamp()') {
                    $column->setAutoUpdate(true);
                }

                $column->setPrecision($row['DATETIME_PRECISION']);
            } elseif ($column instanceof TimeColumnQueryInterface) {
                $column->setPrecision($row['DATETIME_PRECISION']);
            }

            if ($row['COLUMN_DEFAULT'] === null) {
                $column->setDefault(Value::NONE);
            } elseif ($row['COLUMN_DEFAULT'] === 'current_timestamp()') {
                $column->setDefault(Value::NOW);
            } elseif ($column instanceof BoolColumnQueryInterface) {
                if ($row['COLUMN_DEFAULT'] === "'0'") {
                    $column->setDefault(false);
                } elseif ($row['COLUMN_DEFAULT'] === "'1'") {
                    $column->setDefault(true);
                }
            } elseif ($column instanceof IntColumnQueryInterface) {
                $value = intval($row['COLUMN_DEFAULT']);
                $column->setDefault($value);
            } elseif ($column instanceof FloatColumnQueryInterface ||
                $column instanceof FloatColumnQueryInterface
            ) {
                $value = floatval($row['COLUMN_DEFAULT']);
                $column->setDefault($value);
            } else {
                $value = $row['COLUMN_DEFAULT'];
                if (substr($value, 0, 1) === "'" && substr($value, -1) === "'") {
                    $value = substr($value, 1, -1);
                    $value = str_replace("''", "'", $value);
                }
                $column->setDefault($value);
            }

            if ($row['COLUMN_COMMENT'] !== '') {
                $column->setComment($row['COLUMN_COMMENT']);
            }

            $existing[$row['COLUMN_NAME']] = $column;
        }

        return $existing;
    }

    protected function initializeExistingIndexes(): array
    {
        $existing = [];

        $columns = [];
        $unique = [];
        $fulltext = [];
        $comment = [];

        /** @var object */
        $result = $this->getConnection()->execute(
            'SHOW INDEX FROM ' . $this->buildTable($this->getTable()) .
            "WHERE `Key_name` != 'PRIMARY'"
        );

        while ($row = $this->getConnection()->fetch($result)) {
            $columns[$row['Key_name']][] = $row['Column_name'];
            $unique[$row['Key_name']] = ($row['Non_unique'] ? true : false);
            $fulltext[$row['Key_name']] = ($row['Index_type'] === 'FULLTEXT');
            $comment[$row['Key_name']] = $row['Index_comment'];
        }

        foreach ($columns as $key => $value) {
            $index = IndexQuery::fromTableQuery(
                $this,
                $key,
                ...$value,
            );

            if ($unique[$key]) {
                $index->setUnique(true);
            }

            if ($fulltext[$key]) {
                $index->setFulltext(true);
            }

            if ($comment[$key] !== '') {
                $index->setComment($comment[$key]);
            }

            $existing[$key] = $index;
        }

        return $existing;
    }

    protected function initializeExistingForeignKeys(): array
    {
        $existing = [];

        $columns = [];
        $referenceTable = [];
        $referenceColumns = [];

        /** @var object */
        $result = $this->getConnection()->execute(sprintf(
            "SELECT
                `CONSTRAINT_NAME`,
                `COLUMN_NAME`,
                `REFERENCED_TABLE_NAME`,
                `REFERENCED_COLUMN_NAME`
            FROM
                `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
            WHERE
                `TABLE_SCHEMA` = %s AND
                `TABLE_NAME` = %s AND
                `REFERENCED_TABLE_NAME` IS NOT NULL
            ",
            $this->buildScalar($this->getConnection()->getDatabase()),
            $this->buildScalar($this->buildTable($this->getTable(), true))
        ));

        while ($row = $this->getConnection()->fetch($result)) {
            $columns[$row['CONSTRAINT_NAME']][] = $row['COLUMN_NAME'];
            $referenceTable[$row['CONSTRAINT_NAME']] = $row['REFERENCED_TABLE_NAME'];
            $referenceColumns[$row['CONSTRAINT_NAME']][] = $row['REFERENCED_COLUMN_NAME'];
        }

        foreach ($columns as $key => $value) {
            $foreignKey = ForeignKeyQuery::fromTableQuery(
                $this,
                $key,
                ...$value,
            );

            $foreignKey->setReferenceTable($referenceTable[$key]);
            $foreignKey->setReferenceColumns($referenceColumns[$key]);

            /** @var object */
            $result2 = $this->getConnection()->execute(sprintf(
                "SELECT
                    `DELETE_RULE`,
                    `UPDATE_RULE`
                FROM
                    `INFORMATION_SCHEMA`.`REFERENTIAL_CONSTRAINTS`
                WHERE
                    `CONSTRAINT_SCHEMA` = %s AND
                    `CONSTRAINT_NAME` = %s
                LIMIT 1
                ",
                $this->buildScalar($this->getConnection()->getDatabase()),
                $this->buildScalar($key)
            ));

            $row = $this->getConnection()->fetch($result2);

            if ($row) {
                $deleteAction = match ($row['DELETE_RULE']) {
                    'CASCADE' => ReferentialAction::CASCADE,
                    'RESTRICT' => ReferentialAction::RESTRICT,
                    'SET_DEFAULT' => ReferentialAction::SET_DEFAULT,
                    'SET_NULL' => ReferentialAction::SET_NULL,
                    'NO_ACTION' => ReferentialAction::NO_ACTION,
                    default => ReferentialAction::RESTRICT,
                };

                $updateAction = match ($row['UPDATE_RULE']) {
                    'CASCADE' => ReferentialAction::CASCADE,
                    'RESTRICT' => ReferentialAction::RESTRICT,
                    'SET_DEFAULT' => ReferentialAction::SET_DEFAULT,
                    'SET_NULL' => ReferentialAction::SET_NULL,
                    'NO_ACTION' => ReferentialAction::NO_ACTION,
                    default => ReferentialAction::RESTRICT,
                };

                $foreignKey->setDeleteAction($deleteAction);
                $foreignKey->setUpdateAction($updateAction);
            }

            $existing[$key] = $foreignKey;
        }

        return $existing;
    }

    protected function initializeExistingPrimary(): array
    {
        $existing = [];

        /** @var object */
        $result = $this->getConnection()->execute(
            'SHOW INDEX FROM ' . $this->buildTable($this->getTable()) .
            "WHERE `Key_name` = 'PRIMARY'"
        );

        while ($row = $this->getConnection()->fetch($result)) {
            $existing[] = $row['Column_name'];
        }

        return $existing;
    }

    public function getQueryString(): string
    {
        $table = $this->buildTable($this->getTable());

        $query = 'ALTER TABLE ' . $table;

        $ignoreIndexes = [];
        $ignoreForeignKeys = [];

        $queryParts = [];

        // Drop foreign keys that were removed or modified
        foreach ($this->existingForeignKeys as $name => $foreignKey) {
            if ($this->hasForeignKey($name) &&
                $this->getForeignKey($name)->equals($foreignKey)
            ) {
                $ignoreForeignKeys[] = $name;
                continue;
            }

            $name = $this->buildColumn($name);

            $queryParts[] = 'DROP FOREIGN KEY ' . $name;
        }

        // Drop indexes that were removed or modified
        foreach ($this->existingIndexes as $name => $index) {
            if ($this->hasIndex($name) &&
                $this->getIndex($name)->equals($index)
            ) {
                $ignoreIndexes[] = $name;
                continue;
            }

            $name = $this->buildColumn($name);

            $queryParts[] = 'DROP INDEX ' . $name;
        }

        // Drop existing primary key if it has changed
        if ($this->primary !== $this->existingPrimary) {
            $queryParts[] = 'DROP PRIMARY KEY';
        }

        $columnQuery = '';

        // Drop removed columns
        foreach ($this->existingColumns as $name => $column) {
            if (array_key_exists($name, $this->columns)) {
                continue;
            }

            $name = $this->buildColumn($name);

            $queryParts[] = 'DROP COLUMN ' . $name;
        }

        // Add or modify remaining columns
        foreach ($this->columns as $name => $column) {
            if (array_key_exists($name, $this->existingColumns)) {
                if ($column->equals($this->existingColumns[$name])) {
                    continue;
                }

                $queryParts[] = ' ' . $column->getAlterQueryString();
            } else {
                $queryParts[] = ' ' . $column->getCreateQueryString();
            }
        }

        // Update primary key
        if ($this->primary !== $this->existingPrimary) {
            $primary = array_map($this->buildColumn(...), $this->primary);
            $primary = implode(', ', $primary);

            $queryParts[] = 'ADD PRIMARY KEY (' . $primary . ');';
        }

        foreach ($this->indexes as $name => $index) {
            if (in_array($name, $ignoreIndexes)) {
                continue;
            }

            $queryParts[] = 'ADD ' . $index->getDefinitionQueryString();
        }

        foreach ($this->foreignKeys as $name => $foreignKey) {
            if (in_array($name, $ignoreForeignKeys)) {
                continue;
            }

            $queryParts[] = 'ADD ' . $foreignKey->getDefinitionQueryString();
        }

        $query .= ' ' . implode(', ', $queryParts);

        if ($this->getEngine() !== $this->existingEngine) {
            $query .= ', ENGINE ' . $this->buildScalar($this->getEngine());
        }

        if ($this->getCharacterSet() !== $this->existingCharacterSet) {
            $query .= ', CHARACTER SET ' . $this->buildScalar($this->getCharacterSet());
        }

        if ($this->getCollation() !== $this->existingCollation) {
            $query .= ', COLLATE ' . $this->buildScalar($this->getCollation());
        }

        if ($this->getComment() !== $this->existingComment) {
            $query .= ', COMMENT ' . $this->buildScalar($this->getComment() ?? '');
        }

        return $query;
    }
}
