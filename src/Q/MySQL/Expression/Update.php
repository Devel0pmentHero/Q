<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Expression\IUpdate;

/**
 * Represents a MySQL compatible "UPDATE" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Update implements IUpdate {

    use \Q\AnsiSQL\Expression\Update;

    //MySQL's UPDATE is ANSI conform.
}