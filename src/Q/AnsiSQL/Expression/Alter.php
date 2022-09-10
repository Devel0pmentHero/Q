<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IProvider;
use Q\IResult;

/**
 * Trait for AnsiSQL compatible "ALTER" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Alter {

    /**
     * The SQL-statement of the Expression.
     *
     * @var string
     */
    protected string $Statement = "ALTER ";

    /**
     * The statements of the Expression.
     *
     * @var string[]
     */
    protected array $Statements = [];

    /** @inheritDoc */
    public function __construct(protected IProvider $Provider) {
    }

    /** @inheritDoc */
    public function Database(string $Name): static {
        $this->Statement .= "DATABASE {$this->Provider->EscapeField($Name)} ";
        return $this;
    }

    /** @inheritDoc */
    public function Schema(string $Name): static {
        $this->Statement .= "SCHEMA {$this->Provider->SanitizeField($Name)} ";
        return $this;
    }

    /** @inheritDoc */
    public function Table(string $Name): static {
        $this->Statement .= "TABLE {$this->Provider->SanitizeField($Name)} ";
        return $this;
    }

    /** @inheritDoc */
    public function Drop(array $Columns, array $Indexes = []): static {
        foreach($Columns as $Column) {
            $this->Statements[] = "DROP COLUMN {$this->Provider->EscapeField($Column)}";
        }
        foreach($Indexes as $Index) {
            $this->Statements[] = "DROP " . ($Index === "Primary" ? "PRIMARY KEY" : "INDEX {$this->Provider->EscapeField($Index)}");
        }
        return $this;
    }

    /** @inheritDoc */
    public function Execute(bool $Buffered = true): IResult {
        return $this->Provider->Execute((string)$this, $Buffered);
    }

    /** @inheritDoc */
    public function __toString(): string {
        return $this->Statement . \implode(", ", $this->Statements);
    }

    /** @inheritDoc */
    public function __invoke(): null|string|int|float {
        return $this->Execute()->ToValue();
    }
}