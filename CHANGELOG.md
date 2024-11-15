# Change Log

## 1.3.0 - 2024-11-15

### Added

- Added MATCH AGAINST comparison condition.

## 1.2.0 - 2024-03-22

### Added

- Added COALESCE SQL function.

### Fixed

- Fixed spacing issue when using distinct option in functions.

### Changed

- QueryResult will now stringify column values when using them as keys in an array.
- AbstractFunction string arguments will now be treated as columns instead of strings.
- Driver now extends AbstractDriver utility class.

## 1.1.0 - 2023-09-17

### Added

- Added support for DateTime objects when specifying values and conditionals.
- Added support for comparing columns in conditionals.
- Added support for using select queries as columns.
- Added hasGroupBy and hasOrderBy functions to select queries.
- Added missing ForeignKeyNotFoundException class.
- Added PHPStan static analysis.

### Fixed

- Fixed issues with function pathing.
- Added missing types on various class properties and function parameters.
- Added missing $local parameter on ConnectionInterface date functions.
- Fixed various issues picked up by PHPStan.

## 1.0.7 - 2023-09-06

### Fixed

- Fixed wrong variable name in AbstractConnection::fetchValue().

## 1.0.6 - 2023-09-03

### Fixed

- Fixed wrong variable name in ConditionsQueryTrait.
- Fixed issue with 'not' conditions.

## 1.0.5 - 2023-07-15

### Fixed

- Fixed numerous issues with database function classes.
- Fixed various improper capitalizations in namespaces.

## 1.0.4 - 2023-05-09

### Fixed

- Fixed TypeError issue with QueryResult.

## 1.0.3 - 2023-04-30

### Fixed

- Fixed error with getting query result count before rewind.

## 1.0.2 - 2023-04-22

### Fixed

- Dynamic property deprecation error in table IndexQuery.
- Missing types in QueryResult.
- Fixed issue with loading functions.

## 1.0.1 - 2023-03-05

### Fixed

- Fixed bad connection property call in BuildConditionsTrait.
- Fixed missing BuildScalarTrait in SelectQuery sql record.
- Other minor fixes.

## 1.0.0 - 2022-12-30

Initial release.
