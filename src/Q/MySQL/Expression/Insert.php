<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Expression\IInsert;

/**
 * Represents a MySQL compatible "INSERT" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Insert implements IInsert {

    use \Q\AnsiSQL\Expression\Insert;

    /** @inheritDoc */
    public function Execute($Buffered = true): \Q\IResult {
        return $this->Provider->Execute($this->Statement, $Buffered);
    }

    /** @inheritDoc */
    public function ID(): int {
        $this->Execute(false);
        return $this->Provider->LastInsertID();
    }

}