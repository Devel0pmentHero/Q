<?php
declare(strict_types=1);

namespace Q;

/**
 * Interface for prepared statements.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IPreparedStatement {
    
    /**
     * Applies a set of values to the IPreparedStatement.
     *
     * @param mixed  ...$Values The values to apply.
     *
     * @return \Q\IPreparedStatement The instance itself for further chaining.
     */
    public function Apply(mixed ...$Values): IPreparedStatement;
    
    /**
     * Executes the SQL-statement of the IPreparedStatement against a database.
     *
     * @return \Q\IResult
     */
    public function Execute(): IResult;
    
}