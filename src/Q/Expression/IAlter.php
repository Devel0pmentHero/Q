<?php
declare(strict_types=1);

namespace Q\Expression;

use Q\IExpression;
use Q\IProvider;

/**
 * Interface for abstract SQL "ALTER" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IAlter extends IExpression {

    /**
     * Initializes a new instance of the IAlter class.
     *
     * @param \Q\IProvider $Provider Initializes the IAlter with the specified Provider.
     */
    public function __construct(IProvider $Provider);

    /**
     * Alters a database.
     *
     * @param string $Name The  $Name of the database to alter.
     *
     * @return \Q\Expression\IAlter The current instance for further chaining.
     */
    public function Database(string $Name): static;

    /**
     * Alters a schema.
     *
     * @param string $Name The  name of the schema to alter.
     *
     * @return \Q\Expression\IAlter The current instance for further chaining.
     */
    public function Schema(string $Name): static;

    /**
     * Alters a table.
     *
     * @param string $Name The name of the table to alter.
     *
     * @return \Q\Expression\IAlter The current instance for further chaining.
     */
    public function Table(string $Name): static;

    /**
     * Applies multiple "ADD COLUMN/INDEX" statements to the Expression.
     *
     * @param array $Columns The columns to add.
     * @param array $Indexes The indexes to add.
     *
     * @return \Q\Expression\IAlter The current instance for further chaining.
     */
    public function Add(array $Columns, array $Indexes = []): static;

    /**
     * Applies a "RENAME" statement to the Expression.
     *
     * @param string $Name The new name of the entity to rename.
     *
     * @return \Q\Expression\IAlter The current instance for further chaining.
     */
    public function Rename(string $Name): static;

    /**
     * Applies multiple "MODIFY COLUMN" and "RENAME COLUMN/INDEX" statements to the Expression.
     * Implementations should treat string values in the $Columns parameter as renaming attempts.
     *
     * @param array $Columns The columns to modify or rename.
     * @param array $Indexes A key-value separated array of indexes to rename.
     *
     * @return \Q\Expression\IAlter The current instance for further chaining.
     */
    public function Modify(array $Columns, array $Indexes = []): static;

    /**
     * Drops multiple columns and indexes of the table to alter.
     *
     * @param string[] $Columns The columns to drop.
     * @param string[] $Indexes The indexes to drop.
     *
     * @return \Q\Expression\IAlter The current instance for further chaining.
     */
    public function Drop(array $Columns, array $Indexes = []): static;

}