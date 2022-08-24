<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Expression\IDrop;

/**
 * Represents a MsSQL compatible "DROP" Expression.
 *
 * @package Q\MsSQL\Expression
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Drop implements IDrop {

    use \Q\AnsiSQL\Expression\Drop;

    //MsSQL's DROP is ANSI conform.
}