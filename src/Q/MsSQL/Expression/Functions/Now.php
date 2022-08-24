<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression\Functions;

/**
 * SQL function 'NOW()'.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Now extends \Q\AnsiSQL\Expression\Functions\Now {

    /** @inheritDoc */
    public function __toString(): string {
        return "GETDATE()";
    }
    
}