<?php
declare(strict_types=1);

namespace Q\Expression;

use Q\IExpression;
use Q\IProvider;

/**
 * Interface for abstract SQL "SELECT" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface ISelect extends IExpression, \IteratorAggregate {

    /**
     * Initializes a new instance of the ISelect class.
     *
     * @param \Q\IProvider                                  $Provider  Initializes the ISelect with the specified Provider.
     * @param string|array|\Q\Expression\IAggregateFunction ...$Fields Initializes the ISelect with the specified fields to select.
     */
    public function __construct(IProvider $Provider, string|array|IAggregateFunction ...$Fields);

    /**
     * Applies a DISTINCT statement to the ISelect.
     * This method should act like an alternative constructor in implementing classes.
     * To create a distinct select statement, the fields passed to the constructor should be passed to this method instead.
     *
     * @param string|array|\Q\Expression\IAggregateFunction ...$Fields The fields to select.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function Distinct(string|array|IAggregateFunction ...$Fields): static;

    /**
     * Applies one or multiple tables to the ISelect.
     *
     * @param string|array|\Q\Expression\ISelect ...$Tables The tables to select.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function From(string|array|ISelect ...$Tables): static;

    /**
     * Applies a set of conditions to the ISelect.
     *
     * @param array ...$Conditions
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function Where(array ...$Conditions): static;

    /**
     * Applies a JOIN statement to the ISelect.
     *
     * @param string      $Table The table to join.
     * @param string|null $Alias An optional alias for the table to join.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function InnerJoin(string $Table, string $Alias = null): static;

    /**
     * Applies a LEFT (OUTER) JOIN statement to the ISelect.
     *
     * @param string      $Table The table to join.
     * @param string|null $Alias An optional alias for the table to join.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function LeftJoin(string $Table, string $Alias = null): static;

    /**
     * Applies a RIGHT (OUTER) JOIN statement to the ISelect.
     *
     * @param string      $Table The table to join.
     * @param string|null $Alias An optional alias for the table to join.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function RightJoin(string $Table, string $Alias = null): static;

    /**
     * Applies a FULL (OUTER) JOIN statement to the ISelect.
     *
     * @param string      $Table The table to join.
     * @param string|null $Alias An optional alias for the table to join.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function FullJoin(string $Table, string $Alias = null): static;

    /**
     * Applies one or more ON conditions to the ISelect.
     *
     * @param array ...$Fields
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function On(array ...$Fields): static;

    /**
     * Applies a LIMIT condition to the ISelect.
     *
     * @param int $Amount The amount of results to retrieve from the database server.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function Limit(int $Amount): static;

    /**
     * Applies an OFFSET condition to the ISelect.
     *
     * @param int $Index The index from where the results will be retrieved from the database server.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function Offset(int $Index): static;

    /**
     * Applies an ORDER BY condition to the ISelect.
     *
     * @param bool[]|string[] $Fields The fields to order by.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function OrderBy(array $Fields): static;

    /**
     * Applies an UNION condition to the ISelect.
     *
     * @param \Q\Expression\ISelect $Select The sub ISelect to apply.
     * @param bool                  $ALL    Flag indicating whether duplicate values will be included in the result set.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function Union(ISelect $Select, bool $ALL = false): static;

    /**
     * Applies an EXISTS condition to the ISelect.
     *
     * @param \Q\Expression\ISelect $Select The sub ISelect to apply.
     *
     * @return \Q\Expression\ISelect The current instance for further chaining.
     */
    public function Exists(ISelect $Select): static;
}