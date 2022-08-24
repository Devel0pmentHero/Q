<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

/**
 * SQL aggregate function "COUNT()".
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Count extends Distinct {
    
    /**
     * The name of the function.
     */
    protected const Name = "COUNT";
    
}