<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IProvider;
use Q\IResult;

/**
 * Trait for AnsiSQL compatible "CREATE" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Create {

    /**
     * The SQL-statement of the Create.
     *
     * @var string
     */
    protected string $Statement = "CREATE ";

    /** @inheritDoc */
    public function __construct(protected IProvider $Provider) {
    }

    /** @inheritDoc */
    public function Database(string $Name): static {
        $this->Statement .= "DATABASE {$this->Provider->EscapeField($Name)}";
        return $this;
    }

    /** @inheritDoc */
    public function Schema(string $Name): static {
        $this->Statement .= "SCHEMA {$this->Provider->SanitizeField($Name)}";
        return $this;
    }

    /** @inheritDoc */
    public function Table(string $Name): static {
        $this->Statement .= "TABLE {$this->Provider->SanitizeField($Name)} ";
        return $this;
    }

    /** @inheritDoc */
    public function Index(string $Name, bool $Unique = false): static {
        if($Name === "Primary") {
            $this->Statement .= "PRIMARY KEY ";
            return $this;
        }
        $this->Statement .= ($Unique ? " UNIQUE" : "") . " INDEX {$this->Provider->EscapeField($Name)}";
        return $this;
    }

    /** @inheritDoc */
    public function On(string $Table, array $Fields): static {
        $Transformed = [];
        foreach($Fields as $Field => $Size) {
            $Transformed[] = \is_string($Field) ? "{$this->Provider->EscapeField($Field)} ({$Size})" : $this->Provider->EscapeField($Size);
        }
        $this->Statement .= " ON {$this->Provider->SanitizeField($Table)} (" . \implode(", ", $Transformed) . ")";
        return $this;
    }

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