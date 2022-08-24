<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IResult;

/**
 * Trait for AnsiSQL compatible "DELETE" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Delete {

    use Where;

    /**
     * The SQL-statement of the Delete.
     *
     * @var string
     */
    protected string $Statement = "";

    /** @inheritDoc */
    public function From(string $Table): static {
        $this->Statement .= "DELETE FROM {$this->Provider->SanitizeField($Table)} ";
        return $this;
    }

    /** @inheritDoc */
    public function Where(array ...$Conditions): static {
        $this->Statement .= "WHERE {$this->TransformConditions([], ...$Conditions)}";
        return $this;
    }

    //Implementation of IExpression.
    /** @inheritDoc */
    public function Execute(bool $Buffered = true): IResult {
        return $this->Provider->Execute($this->Statement, $Buffered);
    }

    /** @inheritDoc */
    public function __toString(): string {
        return $this->Statement;
    }

    /** @inheritDoc */
    public function __invoke(): null|string|int|float {
        return $this->Execute()->ToValue();
    }

}