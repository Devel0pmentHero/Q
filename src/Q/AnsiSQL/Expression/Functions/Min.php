<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

/**
 * SQL aggregate function "MIN()".
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Min extends Distinct {
    
    /**
     * The name of the function.
     */
    protected const Name = "MIN";
    
}