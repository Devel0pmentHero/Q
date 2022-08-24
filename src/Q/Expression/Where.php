<?php
declare(strict_types=1);

namespace Q\Expression;

/**
 * Enumeration of "WHERE"-clauses.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Where {
    /**
     * "IN"-condition for "WHERE"-clauses.
     */
    public const In = "IN";

    /**
     * "NOT IN"-condition for "WHERE"-clauses.
     */
    public const NotIn = "NOT IN";

    /**
     * "LIKE"-condition for "WHERE"-clauses.
     */
    public const Like = "LIKE";

    /**
     * "BETWEEN"-condition for "WHERE"-clauses.
     */
    public const Between = "BETWEEN";

    /**
     * "NOT BETWEEN"-condition for "WHERE"-clauses.
     */
    public const NotBetween = "NOT BETWEEN";

    /**
     * "REGEXP"-condition for "WHERE"-clauses.
     */
    public const Regex = "REGEXP";

    /**
     * "NOT REGEXP"-condition for "WHERE"-clauses.
     */
    public const NotRegex = "NOT REGEXP";
}

//Alias constants.
\define("In", Where::In);
\define("NotIn", Where::NotIn);
\define("Like", Where::Like);
\define("Between", Where::Between);
\define("NotBetween", Where::NotBetween);
\define("Regex", Where::Regex);
\define("NotRegex", Where::NotRegex);