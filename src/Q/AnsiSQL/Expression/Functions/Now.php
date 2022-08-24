<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

use Q\Expression\IAggregateFunction;
use Q\IProvider;

/**
 * SQL aggregate function "NOW()".
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Now implements IAggregateFunction {

    /** @inheritDoc */
    public function __construct(IProvider $Provider) {
    }

    /** @inheritDoc */
    public function __toString(): string {
        return "NOW()";
    }

}