<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression;

use Q\Expression\IDelete;

/**
 * Represents a PgSQL compatible "DELETE" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Delete implements IDelete {

    use \Q\AnsiSQL\Expression\Delete;

    //PgSQL's DELETE is ANSI conform.
}