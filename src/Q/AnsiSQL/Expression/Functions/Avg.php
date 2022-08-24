<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

/**
 * SQL aggregate function "AVG()".
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Avg extends Distinct {
    
    /**
     * The name of the function.
     */
    protected const Name = "AVG";
    
}