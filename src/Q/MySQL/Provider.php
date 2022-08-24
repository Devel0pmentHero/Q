<?php
declare(strict_types=1);

namespace Q\MySQL;

use Q\MySQL\Expression\Functions\Group;
use Q\SQLException;
use Q\Expression\IAggregateFunction;
use Q\MySQL\Expression\Alter;
use Q\MySQL\Expression\Create;
use Q\MySQL\Expression\Delete;
use Q\MySQL\Expression\Drop;
use Q\MySQL\Expression\Insert;
use Q\MySQL\Expression\Select;
use Q\MySQL\Expression\Update;

/**
 * Abstract data-provider for MySQL and MariaDB databases.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Provider extends \Q\AnsiSQL\Provider {

    /**
     * Regular expression to extract the table- and column name of a field descriptor.
     */
    public const SeparatorExpression = "/^(\w+)\.(\w+)$/";

    /**
     * The format for storing \DateTime values in a MySQL conform format.
     */
    public const Format = "Y-m-d\TH:i:s";

    /**
     * The reserved keywords of the Provider.
     */
    public const Reserved = [
        "READ",
        "WRITE",
        "DELETE",
        "UPDATE",
        "ADD",
        "AND",
        "BEFORE",
        "BY",
        "CALL",
        "CASE",
        "CONDITION",
        "DESC",
        "DESCRIBE",
        "FROM",
        "GROUP",
        "IN",
        "INDEX",
        "INSERT",
        "INTERVAL",
        "IS",
        "KEY",
        "LIKE",
        "LIMIT",
        "LONG",
        "MATCH",
        "NOT",
        "OPTION",
        "OR",
        "ORDER",
        "PARTITION",
        "REFERENCES",
        "SELECT",
        "TABLE",
        "TO",
        "WHERE",
        "BINARY"
    ];

    /**
     * The default port MariaDB-servers usually use.
     */
    public const Port = 3306;

    /**
     * The default charset of the connection collation.
     */
    public const Charset = "utf8mb4";

    /**
     * The quotation character for escaping reserved keywords and field identifiers of the Provider.
     */
    public const Field = "`";

    /**
     * The underlying mysqli connection instance of the Provider.
     *
     * @var \mysqli|null
     */
    protected ?\mysqli $Provider;

    /**
     * The default value indicator of the Provider.
     */
    public const Default = null;

    /**
     * Initializes a new instance of the Provider class.
     *
     * @param string      $Server     Initializes the Provider with the specified address of the target SQL-server.
     * @param string      $User       Initializes the Provider with the specified name of the user of the target SQL-server.
     * @param string      $Password   Initializes the Provider with the specified password of the user of the target SQL-server.
     * @param null|string $Database   This parameter is being ignored due to MySQL doesn't support schemas.
     * @param null|int    $Port       Initializes the Provider with the specified port to use for the connection-socket.
     * @param null|string $Charset    Initializes the Provider with the specified charset of the connection.
     * @param bool        $Persistent Initializes the Provider with the specified flag indicating whether to use a persistent connection.
     *
     * @throws \RuntimeException Thrown if the connection couldn't be established.
     */
    public function __construct(
        string  $Server,
        string  $User,
        string  $Password,
        ?string $Database = null,
        ?int    $Port = self::Port,
        ?string $Charset = self::Charset,
        bool    $Persistent = false
    ) {
        if($Persistent) {
            $Server = "p:{$Server}";
        }
        $this->Provider = new \mysqli($Server, $User, $Password, null, $Port ?? static::Port);
        if($this->Provider->connect_errno > 0) {
            throw new \RuntimeException("Couldn't establish connection to server: {$this->Provider->connect_error}.");
        }
        $this->Provider->set_charset($Charset ?? static::Charset);
    }

    /** @inheritDoc */
    public function LastInsertID(): int {
        return (int)$this->Provider->insert_id;
    }

    /**
     * @inheritDoc
     * @throws \Q\SQLException Thrown if the execution of the statement failed.
     */
    public function Execute(string $Statement, bool $Buffered = true): Result|\Q\Result {

        //Flush previous resultsets.
        while($this->Provider->more_results()) {
            $this->Provider->next_result();
        }

        //Execute statement.
        if(!$this->Provider->real_query($Statement)) {
            throw new SQLException($this->Provider->error, $this->Provider->errno);
        }

        //Check if the result should be buffered
        $Result = $Buffered ? $this->Provider->store_result() : $this->Provider->use_result();

        return $Result instanceof \mysqli_result
            ? new Result($Result, $Buffered)
            : new \Q\Result();
    }

    /** @inheritDoc */
    public function Escape(string $String): string {
        return $this->Provider->real_escape_string($String);
    }

    /** @inheritDoc */
    public function Select(string|array|IAggregateFunction ...$Fields): Select {
        return new Select($this, ...$Fields);
    }

    /** @inheritDoc */
    public function Insert(): Insert {
        return new Insert($this);
    }

    /** @inheritDoc */
    public function Update(string $Table): Update {
        return new Update($this, $Table);
    }

    /** @inheritDoc */
    public function Delete(): Delete {
        return new Delete($this);
    }

    /** @inheritDoc */
    public function Create(): Create {
        return new Create($this);
    }

    /** @inheritDoc */
    public function Alter(): Alter {
        return new Alter($this);
    }

    /** @inheritDoc */
    public function Drop(): Drop {
        return new Drop($this);
    }

    /** @inheritDoc */
    public function Prepare(string $Statement, bool $Buffered = true): Statement {
        return new Statement($this->Provider->prepare($Statement), $Buffered);
    }

    /** @inheritDoc */
    public function Transact(string $Statement, bool $Buffered = false, ?string $Name = null, bool $AutoRollback = true, bool $AutoCommit = true): Transaction {
        return new Transaction($this->Provider, $Statement, $Buffered, $Name, $AutoRollback, $AutoCommit);
    }

    /** @inheritDoc */
    public function Group(...$Fields): Group {
        return new Group($this, ...$Fields);
    }

    /** @inheritDoc */
    public function __destruct() {
        $this->Provider->close();
    }

}