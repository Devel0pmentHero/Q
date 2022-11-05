<?php
declare(strict_types=1);

namespace Q\MsSQL;

use Q\Expression\IAggregateFunction;
use Q\MsSQL\Expression\Alter;
use Q\MsSQL\Expression\Create;
use Q\MsSQL\Expression\Delete;
use Q\MsSQL\Expression\Drop;
use Q\MsSQL\Expression\Functions\CurrentTimestamp;
use Q\MsSQL\Expression\Functions\Group;
use Q\MsSQL\Expression\Functions\Now;
use Q\MsSQL\Expression\Insert;
use Q\MsSQL\Expression\Select;
use Q\MsSQL\Expression\Update;

/**
 * Abstract data-provider for MsSQL databases.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Provider extends \Q\AnsiSQL\Provider {

    /**
     * The reserved keywords of the Provider.
     */
    public const Reserved = ["USERS", "PUBLIC", "FILE", "READ", "DELETE"] + parent::Reserved;

    /**
     * The default port MsSQL-servers usually use.
     */
    public const Port = 1433;

    /**
     * The default charset of the connection collation.
     */
    public const Charset = "UTF-8";

    /**
     * The underlying pgsql connection resource.
     *
     * @var false|resource
     */
    protected mixed $Provider;

    /**
     * Enumeration of characters to escape within SQL statements.
     */
    public const Escape = ["'"];

    /**
     * Enumeration of escaped control characters.
     */
    public const Escaped = ["''"];

    /**
     * Initializes a new instance of the Provider class.
     *
     * @param string      $Server     Initializes the Provider with the specified address of the target SQL-server.
     * @param string      $User       Initializes the Provider with the specified name of the user of the target SQL-server.
     * @param string      $Password   Initializes the Provider with the specified password of the user of the target SQL-server.
     * @param null|string $Database   Initializes the Provider with the specified database of the target SQL-server.
     * @param null|int    $Port       Initializes the Provider with the specified port to use for the connection-socket.
     * @param null|string $Charset    Initializes the Provider with the specified charset of the connection.
     * @param bool        $Persistent Initializes the Provider with the specified flag indicating whether to use a persistent connection.
     *
     * @throws \RuntimeException Thrown if the connection couldn't be established.
     */
    public function __construct(
        public string  $Server,
        string         $User,
        string         $Password,
        public ?string $Database = "master",
        public ?int    $Port = self::Port,
        public ?string $Charset = self::Charset,
        public bool    $Persistent = false
    ) {
        $this->Provider = \sqlsrv_connect(
            "tcp:{$Server}, " . ($Port ?? self::Port),
            [
                "ConnectionPooling"    => $Persistent,
                "UID"                  => $User,
                "PWD"                  => $Password,
                "Database"             => $Database ?? "master",
                "CharacterSet"         => $Charset ?? self::Charset,
                "ReturnDatesAsStrings" => true
            ]
        );
        if($this->Provider === false) {
            throw new \RuntimeException("Couldn't establish connection to server: " . \json_encode(\sqlsrv_errors()));
        }
    }

    /** @inheritDoc */
    public function LastInsertID(): int {
        return (int)$this->Execute("SELECT @@IDENTITY", false)->ToValue();
    }

    /** @inheritDoc */
    public function Execute(string $Statement, bool $Buffered = true): Result|\Q\Result {
        $Result = \sqlsrv_query($this->Provider, $Statement, [], ["Scrollable" => $Buffered ? \SQLSRV_CURSOR_CLIENT_BUFFERED : \SQLSRV_CURSOR_STATIC]);
        return $Result ? new Result($Result, $Buffered) : new \Q\Result();
    }

    /** @inheritDoc */
    public function Call(string $Procedure, array $Arguments): Result|\Q\Result {
        return $this->Execute("EXECUTE {$this->Escape($Procedure)} " . \implode(", ", \array_map(fn($Argument) => $this->Sanitize($Argument), $Arguments)));
    }

    /** @inheritDoc */
    public function EscapeField(string $Field): string {
        return \in_array(\strtoupper(\trim($Field)), self::Reserved)
            ? "[{$Field}]"
            : $Field;
    }

    /** @inheritDoc */
    public function Select(string|IAggregateFunction|array ...$Fields): Select {
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
    public function Prepare(string $Statement, string $Name = ""): Statement {
        return new Statement($this->Provider, $Statement, $Name);
    }

    /** @inheritDoc */
    public function Transact(string $Statement, bool $Buffered = false, ?string $Name = null, bool $AutoRollback = true, bool $AutoCommit = true): Transaction {
        return new Transaction($this->Provider, $Statement, $Buffered, $Name, $AutoRollback, $AutoCommit);
    }

    /** @inheritDoc */
    public function Group(...$Values): Group {
        return new Group($this, ...$Values);
    }

    /** @inheritDoc */
    public function CurrentTimestamp(): CurrentTimestamp {
        return new CurrentTimestamp($this);
    }

    /** @inheritDoc */
    public function Now(): Now {
        return new Now($this);
    }

    /** @inheritDoc */
    public function __destruct() {
        \sqlsrv_close($this->Provider);
    }

}