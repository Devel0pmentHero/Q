<?php
declare(strict_types=1);

namespace Q\MySQL;

use Q\IPreparedStatement;
use Q\SQLException;

/**
 * Class that represents a MySQL compatible prepared statement.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Statement implements IPreparedStatement {

    /**
     * Initializes a new instance of the Statement class.
     *
     * @param null|\mysqli_stmt $Statement Initializes the Statement with the specified prepared-statement.
     * @param bool              $Buffered  Determines whether the result-set will be buffered.
     */
    public function __construct(protected ?\mysqli_stmt $Statement = null, public bool $Buffered = true) {
    }

    /**
     * Applies a set of values to the Statement.
     *
     * @param mixed ...$Values The values to apply.
     *
     * @return \Q\MySQL\Statement The current instance for further chaining.
     */
    public function Apply(mixed ...$Values): self {
        $Types = "";
        foreach($Values as $Value) {
            $Types .= match (\gettype($Value)) {
                "integer" => "i",
                "double" => "d",
                "resource" => "b",
                default => "s"
            };
        }
        if(!$this->Statement->bind_param($Types, ...$Values)) {
            throw new \InvalidArgumentException();
        }
        return $this;
    }

    /**
     * Executes the Statement against the database.
     *
     * @return \Q\MySQL\Result The result of the execution.
     * @throws \Q\SQLException Thrown if the execution of the Statement failed.
     */
    public function Execute(): Result {
        if(!$this->Statement->execute()) {
            throw new SQLException($this->Statement->error, $this->Statement->errno);
        }
        return new Result($this->Statement->get_result());
    }
}