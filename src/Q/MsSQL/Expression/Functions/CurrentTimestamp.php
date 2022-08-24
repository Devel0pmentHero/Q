<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression\Functions;

/**
 * SQL function 'CURRENT_TIMESTAMP()'.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class CurrentTimestamp extends \Q\AnsiSQL\Expression\Functions\CurrentTimestamp {

    /** @inheritDoc */
    public function __toString(): string {
        return "CURRENT_TIMESTAMP";
    }

}