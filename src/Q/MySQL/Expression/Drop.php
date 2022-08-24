<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Expression\IDrop;

/**
 * Represents a MySQL compatible "DROP" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Drop implements IDrop {

    use \Q\AnsiSQL\Expression\Drop {
        \Q\AnsiSQL\Expression\Drop::Database as AnsiSchema;
    }

    /**
     * Flag indicating whether the Database method has been called.
     *
     * @var bool
     */
    private bool $Database = false;

    /** @inheritDoc */
    public function Execute(bool $Buffered = true): \Q\IResult {
        if($this->Database) {
            return new \Q\Result(true);
        }
        return $this->Provider->Execute($this->Statement, $Buffered);
    }

    /** @inheritDoc */
    public function Database(string $Name): static {
        $this->Database = true;
        return $this;
    }

    /** @inheritDoc */
    public function Schema(string $Name): static {
        return $this->AnsiSchema($Name);
    }

}