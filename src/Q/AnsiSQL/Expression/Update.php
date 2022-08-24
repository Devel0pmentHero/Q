<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IProvider;
use Q\IResult;

/**
 * Trait for AnsiSQL compatible "UPDATE" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Update {

    use Where;

    /**
     * The SQL-statement of the Update.
     *
     * @var string
     */
    protected string $Statement = "";

    /** @inheritDoc */
    public function __construct(protected IProvider $Provider, string $Table) {
        $this->Statement .= "UPDATE {$this->Provider->SanitizeField($Table)} ";
    }

    /** @inheritDoc */
    public function Set(array $Fields): static {
        $Statements = [];
        foreach($Fields as $Field => $Value) {
            $Statements[] = "{$this->Provider->EscapeField($Field)} = {$this->Provider->Sanitize($Value)}";
        }
        $this->Statement .= " SET " . \implode(", ", $Statements) . " ";
        return $this;
    }

    /** @inheritDoc */
    public function SetIf(array $Fields): static {
        $Statements = [];
        foreach($Fields as $Field => $Condition) {
            if((bool)\key($Condition)) {
                $Statements[] = "{$this->Provider->EscapeField($Field)} = {$this->Provider->Sanitize(\current($Condition))}";
            }
        }
        $this->Statement .= " SET " . \implode(", ", $Statements) . " ";
        return $this;
    }

    /** @inheritDoc */
    public function Where(array ...$Conditions): static {
        $this->Statement .= "WHERE {$this->TransformConditions([], ...$Conditions)}";
        return $this;
    }

    //Implementation of IExpression.
    /** @inheritDoc */
    public function __invoke(): null|string|int|float {
        return $this->Execute()->ToValue();
    }

    /** @inheritDoc */
    public function Execute(bool $Buffered = true): IResult {
        return $this->Provider->Execute($this->Statement, $Buffered);
    }

    /** @inheritDoc */
    public function __toString(): string {
        return $this->Statement;
    }

}