<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Expression\IDelete;

/**
 * Represents a MsSQL compatible "DELETE" Expression.
 *
 * @package Q\MsSQL\Expression
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Delete implements IDelete {

    use \Q\AnsiSQL\Expression\Delete;

    //MsSQL's DELETE is ANSI conform.
}