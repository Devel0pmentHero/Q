<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression\Functions;

/**
 * Represents a MySQL compatible GROUP_CONCAT function.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Group extends \Q\AnsiSQL\Expression\Functions\Group {

    /**
     * The name of the function.
     */
    protected const Name = "STRING_AGG";
}