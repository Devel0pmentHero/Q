<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Expression\IDelete;

/**
 * Represents a MySQL compatible "DELETE" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Delete implements IDelete {

    use \Q\AnsiSQL\Expression\Delete;

    //MySQL's DELETE is ANSI conform.
}