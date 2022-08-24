<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

use Q\Expression\IAggregateFunction;
use Q\IProvider;

/**
 * Abstract class for distinct SQL aggregate functions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
abstract class Distinct implements IAggregateFunction {

    /**
     * The name of the Distinct.
     */
    protected const Name = "";

    /**
     * Initializes a new instance of the Distinct class.
     *
     * @param mixed $Field    Initializes the Distinct with the specified field the function applies to.
     * @param bool  $Distinct Initializes the Distinct with the specified flag indicating whether to prepend a "DISTINCT" statement to the function.
     */
    public function __construct(protected IProvider $Provider, protected string $Field = "", protected bool $Distinct = false) {
    }

    /**
     * Returns the string representation of the Distinct.
     *
     * @return string The string representation of the Distinct.
     */
    public function __toString(): string {
        return static::Name . "(" . ($this->Distinct ? "DISTINCT " : "") . $this->Provider->EscapeField($this->Field) . ")";
    }

}