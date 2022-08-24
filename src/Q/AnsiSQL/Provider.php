<?php
declare(strict_types=1);

namespace Q\AnsiSQL;

use Q\AnsiSQL\Expression\Functions\Avg;
use Q\AnsiSQL\Expression\Functions\Count;
use Q\AnsiSQL\Expression\Functions\CurrentTimestamp;
use Q\AnsiSQL\Expression\Functions\Max;
use Q\AnsiSQL\Expression\Functions\Min;
use Q\AnsiSQL\Expression\Functions\Now;
use Q\AnsiSQL\Expression\Functions\Sum;
use Q\Expression\IAggregateFunction;
use Q\IModel;
use Q\IProvider;
use Q\IResult;

/**
 * Abstract base class for AnsiSQL compatible DataProviders.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
abstract class Provider implements IProvider {

    /**
     * Regular expression to extract the table- and column name of a field descriptor.
     */
    public const SeparatorExpression = "/^(\w+)\.(\w+)$/";

    /**
     * The separator character indicating database-, schema-, table- and column name of a field descriptor.
     */
    public const Separator = ".";

    /**
     * The format for storing \DateTime values in a MySQL conform format.
     */
    public const Format = "Y-m-d\TH:i:s";

    /**
     * The reserved keywords of the Provider.
     */
    public const Reserved = [
        "ALL",
        "ANALYSE",
        "ANALYZE",
        "AND",
        "ANY",
        "ARRAY",
        "AS",
        "ASC",
        "ASYMMETRIC",
        "AUTHORIZATION",
        "BETWEEN",
        "BOTH",
        "CASE",
        "BINARY",
        "CAST",
        "CHECK",
        "COLLATE",
        "COLUMN",
        "CREATE",
        "CROSS",
        "CURRENT_DATE",
        "CURRENT_ROLE",
        "CURRENT_TIME",
        "CURRENT_TIMESTAMP",
        "DEFAULT",
        "DEFERRABLE",
        "DESC",
        "DISTINCT",
        "DO",
        "ELSE",
        "END",
        "EXCEPT",
        "FALSE",
        "FOR",
        "FOREIGN",
        "FROM",
        "GRANT",
        "GROUP",
        "HAVING",
        "IN",
        "INITIALLY",
        "INNER",
        "INTERSECT",
        "INTO",
        "IS",
        "ISNULL",
        "JOIN",
        "LEADING",
        "LEFT",
        "LIKE",
        "LIMIT",
        "LOCALTIME",
        "LOCALTIMESTAMP",
        "NATURAL",
        "NEW",
        "NOT",
        "NOTNULL",
        "NULL",
        "OFF",
        "OFFSET",
        "OLD",
        "ON",
        "ONLY",
        "OR",
        "ORDER",
        "OUTER",
        "OVERLAPS",
        "PLACING",
        "PRIMARY",
        "REFERENCES",
        "RIGHT",
        "SELECT",
        "SESSION_USER",
        "SIMILAR",
        "SOME",
        "SYMMETRIC",
        "TABLE",
        "THEN",
        "TO",
        "TRAILING",
        "TRUE",
        "UNION",
        "UNIQUE",
        "USER",
        "USING",
        "VERBOSE",
        "WHEN",
        "WHERE"
    ];

    /**
     * Enumeration of characters to escape within SQL statements.
     */
    public const Escape = ["\"", "'", "`", "\\", "/", "\b", "\f", "\b", "\r", "\t", "\u0000", "\u0001", "\u001f"];

    /**
     * Enumeration of escaped control characters.
     */
    public const Escaped = ["\\\"", "\\'", "\\`", "\\\\", "\\/", "\\b", "\\f", "\\b", "\\r", "\\t", "\\u0000", "\\u0001", "\\u001f"];

    /**
     * The quotation character for escaping strings of the Provider.
     */
    public const Quote = "'";

    /**
     * The quotation character for escaping reserved keywords and field identifiers of the Provider.
     */
    public const Field = "\"";

    /**
     * The database null value of the Provider.
     */
    public const NULL    = "NULL";

    /**
     * The default value indicator of the Provider.
     */
    public const Default = "DEFAULT";

    /** @inheritDoc */
    public function Call(string $Procedure, array $Arguments): IResult {
        return $this->Execute("CALL {$this->Escape($Procedure)}(" . \implode(", ", \array_map(fn($Argument) => $this->Sanitize($Argument), $Arguments)) . ")");
    }

    /** @inheritDoc */
    public function Escape(string $String): string {
        return \str_replace(static::Escape, static::Escaped, $String);
    }

    /** @inheritDoc */
    public function EscapeField(string $Field): string {
        $Field = \trim($Field);
        return \in_array(\strtoupper($Field), static::Reserved)
            ? static::Field . $Field . static::Field
            : $Field;
    }

    /**
     * @inheritDoc
     * @throws \JsonException
     */
    public function Sanitize(mixed $Value): string|int {
        if($Value instanceof IModel) {
            return $this->Sanitize($Value->ID());
        }
        if($Value instanceof \DateTime) {
            return static::Quote . $Value->format(static::Format) . static::Quote;
        }
        if($Value instanceof IAggregateFunction) {
            return (string)$Value;
        }
        return match (\gettype($Value)) {
            "string" => $Value === static::Default ? $Value : static::Quote . $this->Escape($Value) . static::Quote,
            "boolean" => (int)$Value,
            "NULL" => static::NULL,
            "object", "array" => static::Quote . \json_encode($Value, \JSON_THROW_ON_ERROR) . static::Quote,
            default => (string)$Value
        };
    }

    /** @inheritDoc */
    public function SanitizeField(string $Field): string {
        $Identifiers = [];
        foreach(\explode(static::Separator, $Field) as $Identifier) {
            $Identifiers[] = $this->EscapeField($Identifier);
        }
        return \implode(static::Separator, $Identifiers);
    }

    /** @inheritDoc */
    public function Min(string $Field, bool $Distinct = false): Min {
        return new Min($this, $Field, $Distinct);
    }

    /** @inheritDoc */
    public function Max(string $Field, bool $Distinct = false): Max {
        return new Max($this, $Field, $Distinct);
    }

    /** @inheritDoc */
    public function Sum(string $Field, bool $Distinct = false): Sum {
        return new Sum($this, $Field, $Distinct);
    }

    /** @inheritDoc */
    public function Count(string $Field, bool $Distinct = false): Count {
        return new Count($this, $Field, $Distinct);
    }

    /** @inheritDoc */
    public function Now(): Now {
        return new Now($this);
    }

    /** @inheritDoc */
    public function Avg(string $Field, bool $Distinct = false): Avg {
        return new Avg($this, $Field, $Distinct);
    }

    /** @inheritDoc */
    public function CurrentTimestamp(): CurrentTimestamp {
        return new CurrentTimestamp($this);
    }

}