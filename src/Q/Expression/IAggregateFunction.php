<?php
declare(strict_types=1);

namespace Q\Expression;

use Q\IProvider;

/**
 * Interface for SQL aggregate functions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IAggregateFunction {

    /**
     * Initializes a new instance of the IAggregateFunction class.
     *
     * @param \Q\IProvider $Provider Initializes the IAggregateFunction with the specified Provider.
     */
    public function __construct(IProvider $Provider);

    /**
     * Returns the string representation of the IAggregateFunction.
     *
     * @return string The string representation of the IAggregateFunction.
     */
    public function __toString(): string;

}