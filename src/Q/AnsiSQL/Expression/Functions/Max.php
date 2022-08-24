<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

/**
 * SQL aggregate function "MAX()".
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Max extends Distinct {
    
    /**
     * The name of the function.
     */
    protected const Name = "MAX";
    
}