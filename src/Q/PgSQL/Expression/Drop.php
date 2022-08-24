<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression;

use Q\Expression\IDrop;

/**
 * Represents a PgSQL compatible "DROP" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Drop implements IDrop {

    use \Q\AnsiSQL\Expression\Drop;

    //Postgres' DROP is ANSI conform.
}