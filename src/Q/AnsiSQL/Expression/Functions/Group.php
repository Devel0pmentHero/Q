<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression\Functions;

use Q\Expression\IAggregateFunction;
use Q\IProvider;

/**
 * Abstract class for GROUPING/-_CONCAT aggregate function.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
abstract class Group implements IAggregateFunction {

    /**
     * The name of the Group.
     */
    protected const Name = "GROUP";

    /**
     * The fields of the Group.
     *
     * @var string[]
     */
    protected array $Fields;

    /**
     * Initializes a new instance of the Group class.
     *
     * @param \Q\IProvider $Provider  Initializes the Group with the specified Provider.
     * @param string       ...$Fields Initializes the Group with the specified fields.
     */
    public function __construct(protected IProvider $Provider, string ...$Fields) {
        $this->Fields = $Fields;
    }

    /**
     * Returns the string representation of the Group.
     *
     * @return string The string representation of the Group.
     */
    public function __toString(): string {
        return self::Name . "(" . \implode(" ,", \array_map(fn(string $Field): string => $this->Provider->SanitizeField($Field), $this->Fields)) . ")";
    }

}