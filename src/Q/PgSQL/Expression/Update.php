<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression;

use Q\Expression\IUpdate;

/**
 * Represents a PgSQL compatible "UPDATE" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Update implements IUpdate {

    use \Q\AnsiSQL\Expression\Update;

    //Postgres' UPDATE is ANSI conform.
}