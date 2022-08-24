<?php
declare(strict_types=1);

namespace Q\Expression;

use Q\IExpression;
use Q\IProvider;

/**
 * Interface for abstract SQL "CREATE" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface ICreate extends IExpression {

    /**
     * Initializes a new instance of the ICreate class.
     *
     * @param \Q\IProvider $Provider Initializes the ICreate with the specified Provider.
     */
    public function __construct(IProvider $Provider);

    /**
     * Creates a database.
     *
     * @param string $Name The name of the database to create.
     *
     * @return \Q\Expression\ICreate The current instance for further chaining.
     */
    public function Database(string $Name): static;

    /**
     * Creates a schema.
     *
     * @param string $Name The name of the schema to create.
     *
     * @return \Q\Expression\ICreate The current instance for further chaining.
     */
    public function Schema(string $Name): static;

    /**
     * Creates a table.
     *
     * @param string $Name The name of the table to create.
     *
     * @return \Q\Expression\ICreate The current instance for further chaining.
     */
    public function Table(string $Name): static;

    /**
     * Creates an index.
     *
     * @param string $Name   The name of the index to create.
     * @param bool   $Unique Flag indicating whether to create an unique index.
     *
     *
     * @return \Q\Expression\ICreate The current instance for further chaining.
     */
    public function Index(string $Name, bool $Unique): static;

    /**
     * Applies an ON statement for creating indices.
     *
     * @param string $Table  The name of the target table to create an index on.
     * @param array  $Fields $Fields The fields of the index.
     *
     * @return \Q\Expression\ICreate The current instance for further chaining.
     */
    public function On(string $Table, array $Fields): static;

}