<?php
declare(strict_types=1);

namespace Q\PgSQL;

use Q\PgSQL\Expression\Functions\Group;
use Q\SQLException;
use Q\Expression\IAggregateFunction;
use Q\PgSQL\Expression\Alter;
use Q\PgSQL\Expression\Create;
use Q\PgSQL\Expression\Delete;
use Q\PgSQL\Expression\Drop;
use Q\PgSQL\Expression\Insert;
use Q\PgSQL\Expression\Select;
use Q\PgSQL\Expression\Update;

/**
 * Abstract data-provider for PostgreSQL databases.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Provider extends \Q\AnsiSQL\Provider {

    /**
     * The default port PgSQL-servers usually use.
     */
    public const Port = 5432;

    /**
     * The default charset of the connection collation.
     */
    public const Charset = "UTF8";

    /**
     * The underlying pgsql connection resource.
     *
     * @var false|resource
     */
    protected mixed $Provider;

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
        string  $Server,
        string  $User,
        string  $Password,
        ?string $Database = null,
        ?int    $Port = self::Port,
        ?string $Charset = self::Charset,
        bool    $Persistent = true
    ) {
        $Credentials    = "host={$Server} port={$Port} user={$User} password={$Password}" . ($Database !== null ? " dbname={$Database}" : "");
        $this->Provider = $Persistent ? \pg_pconnect($Credentials) : \pg_connect($Credentials);
        if($this->Provider === false) {
            throw new \RuntimeException("Couldn't establish connection to server: " . \pg_last_error());
        }
        \pg_set_client_encoding($this->Provider, $Charset ?? static::Charset);
    }

    /** @inheritDoc */
    public function LastInsertID(): int {
        return (int)$this->Execute("SELECT LASTVAL()")->ToValue();
    }

    /**
     * @inheritDoc
     * @throws \Q\SQLException Thrown if the execution of the statement failed.
     */
    public function Execute(string $Statement, bool $Buffered = true): Result|\Q\Result {
        $Result = \pg_query($this->Provider, $Statement);
        if(!$Result) {
            throw new SQLException(\pg_last_error($this->Provider));
        }
        if(\pg_num_rows($Result) > 0) {
            return new Result($Result);
        }
        return new \Q\Result();
    }

    /** @inheritDoc */
    public function Escape(string $String): string {
        return \pg_escape_string($this->Provider, $String);
    }

    /** @inheritDoc */
    public function EscapeField(string $Field): string {
        if($Field === "*") {
            return $Field;
        }
        return \pg_escape_identifier($this->Provider, $Field);
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
    public function Group(...$Fields): Group {
        return new Group(...$Fields);
    }

    /** @inheritDoc */
    public function __destruct() {
        \pg_close($this->Provider);
    }

}