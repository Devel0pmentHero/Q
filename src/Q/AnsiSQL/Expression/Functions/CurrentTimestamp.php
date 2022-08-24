<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

use Q\Expression\IAggregateFunction;
use Q\IProvider;

/**
 * SQL aggregate function "CURRENT_TIMESTAMP()".
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class CurrentTimestamp implements IAggregateFunction {

    /** @inheritDoc */
    public function __construct(IProvider $Provider) {
    }

    /** @inheritDoc */
    public function __toString(): string {
        return "CURRENT_TIMESTAMP()";
    }
}