<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Expression\ISelect;

/**
 * Represents a MySQL compatible "SELECT" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Select implements ISelect {

    use \Q\AnsiSQL\Expression\Select;

    //MySQL's SELECT is (mostly) AnsiSQL conform.

    /** @inheritDoc */
    public function RightJoin(string $Table, string $Alias = null): static {
        return $this->Join("RIGHT", $Table, $Alias);
    }

    /** @inheritDoc */
    public function LeftJoin(string $Table, string $Alias = null): static {
        return $this->Join("LEFT", $Table, $Alias);
    }

    /** @inheritDoc */
    public function FullJoin(string $Table, string $Alias = null): static {
        //FULL OUTER JOIN shim.
        $Right = clone $this;
        $this->LeftJoin($Table, $Alias);
        return $this->Union($Right->RightJoin($Table, $Alias));
    }

}