<?php
declare(strict_types=1);

namespace Q\MsSQL;

use Q\IPreparedStatement;
use Q\SQLException;

/**
 * Class that represents a MsSQL compatible prepared statement.
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
     *
     * @throws \Q\SQLException Thrown if the preparation of the Statement failed.
     */
    public function __construct(private mixed $Provider, public string $Statement) {
        $this->Resource = \sqlsrv_prepare($this->Provider, $Statement, $this->Values);
        if($this->Resource === false) {
            throw new SQLException(\sqlsrv_errors(\SQLSRV_ERR_ERRORS)[0] ?? "Couldn't prepare Statement!");
        }
    }

    /**
     * Applies a specified set of values to the Statement.
     *
     * @param mixed ...$Values The values to apply.
     *
     * @return \Q\MsSQL\Statement The current instance for further chaining.
     */
    public function Apply(mixed ...$Values): self {
        $this->Values = $Values;
        return $this;
    }

    /**
     * Executes the Statement against the database.
     *
     * @return \Q\MsSQL\Result The result of the execution.
     * @throws \Q\SQLException Thrown if the execution of the Statement failed.
     */
    public function Execute(): Result {
        if(!\sqlsrv_execute($this->Resource)) {
            throw new SQLException(\sqlsrv_errors(\SQLSRV_ERR_ERRORS)[0] ?? "Couldn't execute Statement!");
        }
        return new Result($this->Resource);
    }
}