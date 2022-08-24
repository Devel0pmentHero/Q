<?php
declare(strict_types=1);

namespace Q\Expression;

use Q\IExpression;
use Q\IProvider;

/**
 * Interface for abstract SQL "DROP" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IDrop extends IExpression {

    /**
     * Initializes a new instance of the IDrop class.
     *
     * @param \Q\IProvider $Provider Initializes the IDrop with the specified Provider.
     */
    public function __construct(IProvider $Provider);

    /**
     * Drops a database.
     *
     * @param string $Name The name of the database to drop.
     *
     * @return \Q\Expression\IDrop The current instance for further chaining.
     */
    public function Database(string $Name): static;

    /**
     * Drops a schema.
     *
     * @param string $Name The name of the schema to drop.
     *
     * @return \Q\Expression\IDrop The current instance for further chaining.
     */
    public function Schema(string $Name): static;

    /**
     * Drops a table.
     *
     * @param string $Name The name of the table to drop.
     *
     * @return \Q\Expression\IDrop The current instance for further chaining.
     */
    public function Table(string $Name): static;

    /**
     * Drops an index.
     *
     * @param string $Name The name of the index to drop.
     *
     * @return \Q\Expression\IDrop The current instance for further chaining.
     */
    public function Index(string $Name): static;

    /**
     * Applies an ON statement for dropping indices.
     *
     * @param string $Table The name of the target table to drop the index off.
     *
     * @return \Q\Expression\IDrop The current instance for further chaining.
     */
    public function On(string $Table): static;

}