<?php
declare(strict_types=1);

namespace Q;

/**
 * Interface for result sets retrieved by data Providers.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IResult extends \Traversable {

    /**
     * Retrieves a row of the IResult as an numeric array.
     *
     * @return string[]|null The row at the current position within the IResult; otherwise, null.
     */
    public function ToArray(): ?array;

    /**
     * Retrieves a row of the IResult as an associative array.
     *
     * @return string[]|null The row at the current position within the IResult; otherwise, null.
     */
    public function ToMap(): ?array;

    /**
     * Retrieves a row of the IResult as a single value.
     *
     * @return null|string|int|float The value of the row at the current position within the IResult; otherwise, null.
     */
    public function ToValue(): null|string|int|float;

    /**
     * Frees all resources allocated by the IResult.
     */
    public function Free(): void;

    /**
     * Gets a value indicating whether the previous executed SQL-statement was successful.
     *
     * @return bool True if the previous SQL-statement has been executed successfully; otherwise, false.
     */
    public function Successful(): bool;

    /**
     * Retrieves a row of the IResult as a single value.
     *
     * @return null|string|int|float The value of the row at the current position within the IResult; otherwise, null.
     */
    public function __invoke(): null|string|int|float;
}
