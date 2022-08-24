<?php
declare(strict_types=1);

namespace Q\PgSQL;

use Q\IPreparedStatement;
use Q\SQLException;

/**
 * Class that represents a PostgreSQL compatible prepared statement.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Statement implements IPreparedStatement {

    /**
     * The underlying resource returned of @see \pg_prepare()
     *
     * @var false|resource
     */
    private $Resource;

    /**
     * The values of the Statement.
     *
     * @var array
     */
    public array $Values = [];

    /**
     * Initializes a new instance of the Statement class.
     *
     * @param mixed  $Provider  Initializes the Statement with the specified connection resource.
     * @param string $Statement Initializes the Statement with the specified SQL-Statement.
     * @param string $Name      Initializes the Statement with the specified optional name.
     *
     * @throws \Q\SQLException Thrown if the preparation of the statement failed.
     */
    public function __construct(private mixed $Provider, public string $Statement, public string $Name = "") {
        $this->Resource = \pg_prepare($this->Provider, $Name, $Statement);
        if($this->Resource === false) {
            throw new SQLException(\pg_last_error($Provider));
        }
    }

    /**
     * Applies a specified set of values to the Statement.
     *
     * @param mixed ...$Values The values to apply.
     *
     * @return \Q\PgSQL\Statement The current instance for further chaining.
     */
    public function Apply(mixed ...$Values): self {
        $this->Values[] = $Values;
        return $this;
    }

    /**
     * Executes the Statement against the database.
     *
     * @return \Q\PgSQL\Result The result of the execution.
     * @throws \Q\SQLException Thrown if the execution of the Statement failed.
     */
    public function Execute(): Result {
        $Result = \pg_execute($this->Resource, $this->Statement, $this->Values);
        if($Result === false) {
            throw new SQLException(\pg_last_error($this->Provider));
        }
        return new Result($Result);
    }
}