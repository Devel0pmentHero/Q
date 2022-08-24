<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Expression\IUpdate;

/**
 * Represents a MsSQL compatible "UPDATE" Expression.
 *
 * @package Q\MsSQL\Expression
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Update implements IUpdate {

    use \Q\AnsiSQL\Expression\Update;
    //MsSQL's UPDATE is ANSI conform.
}