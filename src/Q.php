<?php
declare(strict_types=1);

use Q\AnsiSQL\Provider;
use Q\Expression\IAggregateFunction;
use Q\Expression\IAlter;
use Q\Expression\ICreate;
use Q\Expression\IDelete;
use Q\Expression\IDrop;
use Q\Expression\IInsert;
use Q\Expression\ISelect;
use Q\Expression\IUpdate;
use Q\IPreparedStatement;
use Q\IProvider;
use Q\IResult;
use Q\ITransaction;

/**
 * Provides abstract database access.
 *
 * @package Q
 * @author  Kerry Holz <Q@DevelopmentHero.de>
 */
class Q {

    /**
     * The null value of the current data Provider.
     *
     * @var null|string
     */
    public static ?string $NULL = null;

    /**
     * The field separator character of the current data Provider.
     *
     * @var string
     */
    public static string $Separator = Provider::Separator;

    /**
     * The quotation character for escaping strings of the current data Provider.
     *
     * @var string
     */
    public static string $Quote = Provider::Quote;

    /**
     * The quotation character for escaping reserved keywords and field identifiers of the current data Provider.
     *
     * @var string
     */
    public static string $Field = Provider::Field;


    /**
     * The default value of the current data Provider.
     *
     * @var null|string
     */
    public static ?string $Default = null;

    /**
     * The IProvider of the data Provider
     *
     * @var  null|\Q\IProvider
     */
    public static ?IProvider $Provider = null;

    /**
     * Connects to a specified database.
     *
     * @param \Q\IProvider|string $Provider   The data Provider to use to connect to the target database.
     * @param string              $Server     The server address of the target database to connect.
     * @param null|int            $Port       The port of the target database to connect.
     * @param string              $User       The user of the target database to connect.
     * @param string              $Password   The password of the target database to connect.
     * @param null|string         $Database   The database to use of the target database to connect.
     * @param bool                $Persistent Flag indicating whether to use connection pooling.
     *
     * @return \Q\IProvider
     */
    public static function Connect(
        IProvider|string $Provider,
        string           $Server = "localhost",
        int              $Port = null,
        string           $User = "",
        string           $Password = "",
        ?string          $Database = null,
        bool             $Persistent = false
    ): IProvider {
        if($Provider instanceof IProvider) {
            return static::$Provider = $Provider;
        }

        static::$Provider = new $Provider($Server, $User, $Password, $Database, $Port, null, $Persistent);

        //Populate database specific values.
        static::$NULL      = static::$Provider::NULL;
        static::$Separator = static::$Provider::Separator;
        static::$Quote     = static::$Provider::Quote;
        static::$Field     = static::$Provider::Field;
        static::$Default   = static::$Provider::default;
        return static::$Provider;
    }

    /**
     * Retrieves the last auto generated ID of an INSERT-SQL-Statement.
     *
     * @return int The last insert ID.
     */
    public static function LastInsertID(): int {
        return static::$Provider->LastInsertID();
    }

    /**
     * Executes a SQL-Statement on the database-server.
     *
     * @param string $Statement The SQL-Statement to execute.
     * @param bool   $Buffered  Flag indicating whether the result-set will be buffered.
     *
     * @return IResult The result-set yielding the values the SQL-server returned from the specified statement.
     */
    public static function Execute(string $Statement, bool $Buffered = true): IResult {
        return static::$Provider->Execute($Statement, $Buffered);
    }

    /**
     * Calls a Stored-Procedure on the database-server.
     *
     * @param string $Procedure      The Stored-Procedure to call.
     * @param mixed  $Parameters,... A list of arguments to pass to the procedure.
     *
     * @return IResult The result-set.
     */
    public static function Call(string $Procedure, ...$Parameters): IResult {
        return static::$Provider->Call($Procedure, $Parameters);
    }

    /**
     * Escapes special characters in a string according to the current database-specification.
     *
     * @param string $String The string to escape.
     *
     * @return string The escaped string.
     */
    public static function Escape(string $String): string {
        return static::$Provider->Escape($String);
    }

    /**
     * Escapes special characters or reserved words in a field according to the current database-specification.
     *
     * @param string $Field The field to escape.
     *
     * @return string The escaped field.
     */
    public static function EscapeField(string $Field): string {
        return static::$Provider->EscapeField($Field);
    }

    /**
     * Sanitizes a value according to the current database-specification.
     *
     * @param mixed $Value The value to sanitize.
     *
     * @return mixed The sanitized value.
     */
    public static function Sanitize(mixed $Value): mixed {
        return static::$Provider->Sanitize($Value);
    }

    /**
     * Sanitizes special characters or reserved words in a field according to the current database-specification.
     *
     * @param mixed $Field The field to sanitize.
     *
     * @return string The sanitized field.
     */
    public static function SanitizeField(mixed $Field): string {
        return static::$Provider->SanitizeField($Field);
    }

    /**
     * Prepares a SQL-statement to execute on a SQL-server.
     *
     * @param string $Statement The SQL-statement to execute.
     * @param bool   $Buffered  Determines whether the result-set will be buffered.
     *
     * @return \Q\IPreparedStatement The prepared statement to execute.
     */
    public static function Prepare(string $Statement, bool $Buffered = true): IPreparedStatement {
        return static::$Provider->Prepare($Statement, $Buffered);
    }

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
    public static function Transact(string $Statement, bool $Buffered = false, ?string $Name = null, bool $AutoRollback = false, bool $AutoCommit = false): ITransaction {
        return static::$Provider->Transact($Statement, $Buffered, $Name, $AutoRollback, $AutoCommit);
    }

    /**
     * Factory method that creates a new instance of the ISelect class according the configured data Provider.
     *
     * @param string|array|\Q\Expression\IAggregateFunction ...$Fields The fields to select.
     *
     * @return \Q\Expression\ISelect
     */
    public static function Select(string|array|IAggregateFunction ...$Fields): ISelect {
        return static::$Provider->Select(...$Fields);
    }

    /**
     * Factory method that creates a new instance of the IInsert class according the configured data Provider.
     *
     * @return \Q\Expression\IInsert
     */
    public static function Insert(): IInsert {
        return static::$Provider->Insert();
    }

    /**
     * Factory method that creates a new instance of the IUpdate class according the configured data Provider.
     *
     * @param string $Table The name of the table tu update.
     *
     * @return \Q\Expression\IUpdate An
     */
    public static function Update(string $Table): IUpdate {
        return static::$Provider->Update($Table);
    }

    /**
     * Factory method that creates a new instance of the IDelete class according the configured data Provider.
     *
     * @return \Q\Expression\IDelete
     */
    public static function Delete(): IDelete {
        return static::$Provider->Delete();
    }

    /**
     * Factory method that creates a new instance of the ICreate class according the configured data Provider.
     *
     * @return \Q\Expression\ICreate
     */
    public static function Create(): ICreate {
        return static::$Provider->Create();
    }

    /**
     * Factory method that creates a new instance of the IAlter class according the configured data Provider.
     *
     * @return \Q\Expression\IAlter
     */
    public static function Alter(): IAlter {
        return static::$Provider->Alter();
    }

    /**
     * Factory method that creates a new instance of the IDrop class according the configured data Provider.
     *
     * @return \Q\Expression\IDrop
     */
    public static function Drop(): IDrop {
        return static::$Provider->Drop();
    }

    /**
     * Factory method that creates new "MIN()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to get the lowest value of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function Min(string $Field, bool $Distinct = false): IAggregateFunction {
        return static::$Provider->Min($Field, $Distinct);
    }

    /**
     * Factory method that creates new "MAX()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to get the highest value of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function Max(string $Field, bool $Distinct = false): IAggregateFunction {
        return static::$Provider->Max($Field, $Distinct);
    }

    /**
     * Factory method that creates new "SUM()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to sum the values of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function Sum(string $Field, bool $Distinct = false): IAggregateFunction {
        return static::$Provider->Sum($Field, $Distinct);
    }

    /**
     * Factory method that creates new "COUNT()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to count the rows of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function Count(string $Field, bool $Distinct = false): IAggregateFunction {
        return static::$Provider->Count($Field, $Distinct);
    }

    /**
     * Factory method that creates new "NOW()"-IAggregateFunction according the current target database.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function Now(): IAggregateFunction {
        return static::$Provider->Now();
    }

    /**
     * Factory method that creates new "AVG()"-IAggregateFunction according the current target database.
     *
     * @param string $Field    The field to get the average value of.
     * @param bool   $Distinct Flag indicating whether to prepend a "DISTINCT"-statement.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function Avg(string $Field, bool $Distinct = false): IAggregateFunction {
        return static::$Provider->Avg($Field, $Distinct);
    }

    /**
     * Factory method that creates new "GROUP_CONCAT()/GROUPING()"-IAggregateFunction according the current target database.
     *
     * @param mixed ...$Fields The field to group.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function Group(...$Fields): IAggregateFunction {
        return static::$Provider->Avg(...$Fields);
    }

    /**
     * Factory method that creates new "CURRENT_TIMESTAMP()"-IAggregateFunction according the current target database.
     *
     * @return \Q\Expression\IAggregateFunction A new instance of the IAggregateFunction-implementation compatible to the current target database.
     */
    public static function CurrentTimestamp(): IAggregateFunction {
        return static::$Provider->CurrentTimestamp();
    }

}

//Initialize facade.
if(\defined("QConfig")) {
    Q::Connect(
        QConfig["Provider"],
        QConfig["Server"],
        QConfig["Port"],
        QConfig["User"],
        QConfig["Password"],
        QConfig["Database"],
        QConfig["Persistent"] ?? false
    );
}