<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

/**
 * SQL aggregate function "SUM()".
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Sum extends Distinct {
    
    /**
     * The name of the function.
     */
    protected const Name = "SUM";
    
}