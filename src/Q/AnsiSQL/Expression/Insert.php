<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IProvider;
use Q\IResult;

/**
 * Trait for AnsiSQL compatible "INSERT" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Insert {

    /**
     * The SQL-statement of the Insert\MariaDB.
     *
     * @var string
     */
    protected string $Statement = "";

    /**
     * The fields of the Insert\MariaDB.
     *
     * @var string[]
     */
    protected ?array $Fields = [];

    /** @inheritDoc */
    public function __construct(protected IProvider $Provider) {
    }

    /** @inheritDoc */
    public function Into(string $Table, array $Fields = null): static {
        $this->Statement .= "INSERT INTO {$this->Provider->SanitizeField($Table)} ";
        $this->Fields    = $Fields;
        return $this;
    }

    /** @inheritDoc */
    public function Values(array $Values, array ...$Multiple): static {
        $this->Statement .= "("
                            . \implode(
                                ", ",
                                \array_map(fn($Field): string => $this->Provider->EscapeField($Field), $this->Fields ?? \array_keys($Values))
                            )
                            . ") VALUES ("
                            . \implode(
                                ", ",
                                \array_map(fn($Value) => $this->Provider->Sanitize($Value), \array_values($Values))
                            )
                            . ")";
        foreach($Multiple as $AdditionalValues) {
            $this->Statement .= ", ("
                                . \implode(
                                    ", ",
                                    \array_map(fn($Value) => $this->Provider->Sanitize($Value), \array_values($AdditionalValues))
                                )
                                . ")";
        }
        return $this;
    }

    /** @inheritDoc */
    public function ID(): int {
        $this->Execute();
        return $this->Provider->LastInsertID();
    }

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