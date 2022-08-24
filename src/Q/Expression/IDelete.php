<?php
declare(strict_types=1);

namespace Q\Expression;

use Q\IExpression;
use Q\IProvider;

/**
 * Interface for abstract SQL "DELETE" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IDelete extends IExpression {

    /**
     * Initializes a new instance of the IDelete class.
     *
     * @param \Q\IProvider $Provider Initializes the IDelete with the specified Provider.
     */
    public function __construct(IProvider $Provider);

    /**
     * Applies one table to the IDelete.
     *
     * @param string $Table The table to delete a row from.
     *
     * @return \Q\Expression\IDelete The current instance for further chaining.
     */
    public function From(string $Table): static;

    /**
     * Applies a set of conditions to the IDelete.
     *
     * @param mixed array ...$Conditions The conditions to apply.
     *
     * @return \Q\Expression\IDelete The current instance for further chaining.
     */
    public function Where(array ...$Conditions): static;

}