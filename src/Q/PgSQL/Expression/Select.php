<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression;

use Q\Expression\ISelect;

/**
 * Represents a PgSQL compatible "SELECT" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Select implements ISelect {

    use \Q\AnsiSQL\Expression\Select;

    //Postgres' SELECT is ANSI conform.
}