<?php
declare(strict_types=1);

namespace Q\Expression;

use Q\IExpression;
use Q\IProvider;

/**
 * Interface for abstract SQL "UPDATE" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IUpdate extends IExpression {

    /**
     * Initializes a new instance of the IUpdate class.
     *
     * @param \Q\IProvider $Provider Initializes the IUpdate with the specified Provider.
     * @param string       $Table    Initializes the IUpdate with the specified table to update.
     */
    public function __construct(IProvider $Provider, string $Table);

    /**
     * Applies one or multiple fields and values to update to the IUpdate.
     *
     * @param array $Fields The fields and values to update.
     *
     * @return \Q\Expression\IUpdate The current instance for further chaining.
     */
    public function Set(array $Fields): static;

    /**
     * Applies one or multiple fields to conditionally update or key-value-pairs to the IUpdate.
     *
     * @param mixed $Fields The fields to update.
     *
     * @return \Q\Expression\IUpdate The current instance for further chaining.
     */
    public function SetIf(array $Fields): static;

    /**
     * Applies a set of conditions to the IUpdate.
     *
     * @param array ...$Conditions
     *
     * @return \Q\Expression\IUpdate The current instance for further chaining.
     */
    public function Where(array ...$Conditions): static;

}