<?php
declare(strict_types=1);

namespace Q;

use Q\Expression\IAggregateFunction;
use Q\Expression\IAlter;
use Q\Expression\ICreate;
use Q\Expression\IDelete;
use Q\Expression\IDrop;
use Q\Expression\IInsert;
use Q\Expression\ISelect;
use Q\Expression\IUpdate;

/**
 * Interface for vendor specific SQL data-providers.
 * Provides functionality for executing queries and procedures on a database.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IProvider {

    /**
     * Initializes a new instance of the IProvider class.
     *
     * @param string      $Server     Initializes the IProvider with the specified address of the target SQL-server.
     * @param string      $User       Initializes the IProvider with the specified name of the user of the target SQL-server.
     * @param string      $Password   Initializes the IProvider with the specified password of the user of the target SQL-server.
     * @param null|string $Database   Initializes the IProvider with the specified database of the target SQL-server.
     * @param null|int    $Port       Initializes the IProvider with the specified port to use for the connection-socket.
     * @param null|string $Charset    Initializes the IProvider with the specified charset of the collation of the connection.
     * @param bool        $Persistent Initializes the IProvider with the specified flag indicting whether to use a persistent connection.
     */
    public function __construct(string $Server, string $User, string $Password, ?string $Database, ?int $Port, ?string $Charset, bool $Persistent = false);

    /**
     * Retrieves the last auto generated ID of an INSERT-SQL-Statement.
     *
     * @return int The last insert ID.
     */
    public function LastInsertID(): int;

    /**
     * Executes a SQL-query on a SQL-server.
     *
     * @param string $Statement The SQL-statement to execute.
     * @param bool   $Buffered  Flag indicating whether to buffer the result set.
     *
     * @return \Q\IResult The result-set yielding the values the SQL-server returned from the specified statement.
     */
    public function Execute(string $Statement, bool $Buffered = true): IResult;

    /**
     * Executes an stored procedure on a SQL-server.
     *
     * @param string $Procedure The name of the procedure to execute.
     * @param array  $Arguments The list of arguments to pass to the procedure.
     *
     * @return \Q\IResult The IResult containing the results of the executed procedure.
     */
    public function Call(string $Procedure, array $Arguments): IResult;

    /**
     * Escapes special characters in a string according to the current database-specification.
     *
     * @param string $String The string to escape.
     *
     * @return string The escaped string.
     */
    public function Escape(string $String): string;

    /**
     * Escapes a field if the field contains a reserved word according to the current database-specification.
     *
     * @param string $Field The field to escape.
     *
     * @return string The escaped field.
     */
    public function EscapeField(string $Field): string;

    /**
     * Sanitizes a value according to the current database-specification.
     *
     * @param mixed $Value The value to sanitize.
     *
     * @return mixed The sanitized value.
     */
    public function Sanitize(mixed $Value): mixed;

    /**
     * Escapes reserved words in a pair of table and field, separated by the "table.column" separator of the current current target database or a single field
     * according to the current database-specification.
     *
     * @param string $Field The pair of table and field to sanitize.
     *
     * @return string The sanitized pair of table and field.
     */
    public function SanitizeField(string $Field): string;

    /**
     * Factory method that creates a new instance of the ISelect class according the current target database.
     *
     * @param string|array|\Q\Expression\IAggregateFunction ...$Fields The fields to select.
     *
     * @return \Q\Expression\ISelect An ISelect Expression compatible to the current target database.
     */
    public function Select(string|array|IAggregateFunction ...$Fields): ISelect;

    /**
     * Factory method that creates a new instance of the IInsert class according the current target database.
     *
     * @return \Q\Expression\IInsert An IInsert Expression compatible to the current target database.
     */
    public function Insert(): IInsert;

    /**
     * Factory method that creates a new instance of the IUpdate class according the current target database.
     *
     * @param string $Table The name of the table to update.
     *
     * @return \Q\Expression\IUpdate An IUpdate Expression compatible to the current target database.
     */
    public function Update(string $Table): IUpdate ;

    /**
     * Factory method that creates a new instance of the IDelete class according the current target database.
     *
     * @return \Q\Expression\IDelete An IDelete Expression compatible to the current target database.
     */
    public function Delete(): IDelete ;

    /**
     * Factory method that creates a new instance of the ICreate class according the current target database.
     *
     * @return \Q\Expression\ICreate An ICreate Expression compatible to the current target database.
     */
    public function Create(): ICreate ;

    /**
     * Factory method that creates a new instance of the IAlter class according the current target database.
     *
     * @return \Q\Expression\IAlter An IAlter Expression compatible to the current target database.
     */
    public function Alter(): IAlter;

    /**
     * Factory method that creates a new instance of the IDrop class according the current target database.
     *
     * @return \Q\Expression\IDrop An ICreate Expression compatible to the current target database.
     */
    public function Drop(): IDrop;

    /**
     * Prepares a SQL-statement to execute on a SQL-server.
     *
     * @param string $Statement The SQL-statement to execute.
     *
     * @return \Q\IPreparedStatement The prepared statement to execute.
     */
    public function Prepare(string $Statement): IPreparedStatement;

    /**
     * Begins a SQL-transaction to execute on a SQL-server.
     *
     * @param string      $Statement    The SQL-transaction-statement to execute.
     * @param bool        $Buffered     Determines whether the result-set will be buffered.
     * @param null|string $Name         The name of the SQL-statement.
     * @param bool        $AutoRollback Determines whether the changes of the transaction will be rolled back automatically if an error has
     *                                  occurred or the transaction was unsuccessful.
     * @param bool        $AutoCommit   Determines whether the changes of the transaction will be committed automatically if the
     *                                  transaction was successful.
     *
     * @return \Q\ITransaction The transaction to execute.
     */
    public function Transact(string $Statement, bool $Buffered = false, ?string $Name = null, bool $AutoRollback = true, bool $AutoCommit = true): ITransaction;

    /**
     * Factory method that creates new "MIN()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to get the lowest value of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function Min(string $Field, bool $Distinct = false): IAggregateFunction;

    /**
     * Factory method that creates new "MAX()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to get the highest value of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function Max(string $Field, bool $Distinct = false): IAggregateFunction;

    /**
     * Factory method that creates new "SUM()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to sum the values of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function Sum(string $Field, bool $Distinct = false): IAggregateFunction;

    /**
     * Factory method that creates new "COUNT()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to count the rows of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function Count(string $Field, bool $Distinct = false): IAggregateFunction;

    /**
     * Factory method that creates new "NOW()"-IAggregateFunction according the current target database.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function Now(): IAggregateFunction;

    /**
     * Factory method that creates new "AVG()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to get the average value of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function Avg(string $Field, bool $Distinct = false): IAggregateFunction;

    /**
     * Factory method that creates new "GROUP_CONCAT()/GROUPING()"-IAggregateFunction according the current target database.
     *
     * @param mixed ...$Fields
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function Group(...$Fields): IAggregateFunction;

    /**
     * Factory method that creates new "CURRENT_TIMESTAMP()"-IAggregateFunction according the current target database.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public function CurrentTimestamp(): IAggregateFunction;

    /**
     * Closes the connection of the Provider if it runs out of scope.
     */
    public function __destruct();

}