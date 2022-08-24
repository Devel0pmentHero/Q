<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Expression\ISelect;

/**
 * Represents a MsSQL compatible "SELECT" Expression.
 *
 * @package Q\MsSQL\Expression
 * @author  Kerry <Q@DevelopmentHero.de>
 */
final class Select implements ISelect {

    use \Q\AnsiSQL\Expression\Select;

    //MsSQL's SELECT is ANSI conform.
}