<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IProvider;
use Q\IResult;

/**
 * Trait for AnsiSQL compatible "DROP" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Drop {

    /**
     * The SQL-statement of the Drop.
     *
     * @var string
     */
    protected string $Statement = "DROP ";

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
        $this->Statement .= "SCHEMA {$this->Provider->EscapeField($Name)}";
        return $this;
    }

    /** @inheritDoc */
    public function Table(string $Name): static {
        $this->Statement .= "TABLE {$this->Provider->EscapeField($Name)}";
        return $this;
    }

    /** @inheritDoc */
    public function Index(string $Name): static {
        $this->Statement .= "INDEX {$this->Provider->EscapeField($Name)}";
        return $this;
    }

    /** @inheritDoc */
    public function On(string $Table): static {
        $this->Statement .= " ON {$this->Provider->SanitizeField($Table)}";
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