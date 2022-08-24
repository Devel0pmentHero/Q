<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Collation;
use Q\MsSQL\Provider;
use Q\Type;

/**
 * Abstract baseclass for MsSQL compatible Expressions that work with tables.
 *
 * @package Q\MsSQL\Expression
 * @author  Kerry <Q@DevelopmentHero.de>
 */
abstract class Table extends \Q\AnsiSQL\Expression\Table {

    /**
     * Enumeration of supported MsSQL type mappings.
     */
    public const Collations = [
        Collation::ASCII                     => "Latin1_General_100_CI_AI",
        Collation::ASCII | Collation::Binary => "Latin1_General_100_BIN2",
        Collation::UTF8                      => "Latin1_General_100_CI_AI_SC_UTF8",
        Collation::UTF8 | Collation::Binary  => "Latin1_General_100_BIN2_UTF8",
        Collation::UTF16                     => "Latin1_General_100_CI_AI_SC_UTF8",
        Collation::UTF16 | Collation::Binary => "Latin1_General_100_BIN2_UTF8",
        Collation::UTF32                     => "Latin1_General_100_CI_AI_SC_UTF8",
        Collation::UTF32 | Collation::Binary => "Latin1_General_100_BIN2_UTF8"
    ];

    /**
     * Enumeration of MsSQL specified type mappings.
     */
    public const Types = [
        Type::TinyInt    => "TINYINT",
        Type::SmallInt   => "SMALLINT",
        Type::Int        => "INT",
        Type::BigInt     => "BIGINT",
        Type::Boolean    => "TINYINT",
        Type::Decimal    => "DECIMAL",
        Type::Float      => "REAL",
        Type::Double     => "DOUBLE PRECISION",
        Type::Char       => "CHAR",
        Type::VarChar    => "VARCHAR",
        Type::TinyText   => "VARCHAR(255)",
        Type::Text       => "VARCHAR(MAX)",
        Type::MediumText => "VARCHAR(MAX)",
        Type::LongText   => "VARCHAR(MAX)",
        Type::Timestamp  => "TIMESTAMP",
        Type::Date       => "DATE",
        Type::Time       => "TIME",
        Type::DateTime   => "DATETIME",
        Type::TinyBlob   => "VARBINARY(255)",
        Type::Blob       => "VARBINARY(65535)",
        Type::MediumBlob => "VARBINARY(16777215)",
        Type::LongBlob   => "VARBINARY(MAX)"
    ];

    /** @inheritDoc */
    public function Field(
        string  $Name,
        int     $Type,
        bool    $Nullable = false,
        bool    $AutoIncrement = false,
        mixed   $Default = "",
        ?int    $Collation = null,
        ?int    $Size = null,
        ?string $OnUpdate = null
    ): string {

        $Field = [$this->Provider->EscapeField($Name)];
        $Limit = "";
        if($Size !== null) {
            $Limit = $Size > 8000 ? "(MAX)" : "($Size)";
        }

        //Create type and collation.
        if($Collation !== null) {
            if($Collation & ~Collation::ASCII & ~Collation::Binary) {
                $Field[] = match ($Type & ~Type::Unsigned) {
                    Type::Char,
                    Type::VarChar => "N" . static::Types[$Type & ~Type::Unsigned] . $Limit,
                    Type::TinyText,
                    Type::Text,
                    Type::MediumText,
                    Type::LongText => "N" . static::Types[$Type & ~Type::Unsigned],
                    default => ""
                };
            } else {
                $Field[] = static::Types[$Type & ~Type::Unsigned] . match ($Type) {
                        Type::Char, Type::VarChar => $Limit,
                        default => ""
                    };
            }
            $Field[] = "COLLATE " . static::Collations[$Collation];
        } else {
            $Field[] = static::Types[$Type & ~Type::Unsigned] . match ($Type) {
                    Type::Char, Type::VarChar => $Limit,
                    default => ""
                };
        }

        $Field[] = $Nullable ? Provider::NULL : "NOT " . Provider::NULL;
        if($Default !== "" && ($Type & ~Type::Unsigned) !== Type::Timestamp) {
            $Field[] = Provider::Default . " {$this->Provider->Sanitize($Default)}";
        }
        if($AutoIncrement) {
            $Field[] = "IDENTITY (1, 1)";
        }
        if($OnUpdate !== null) {
            $Field[] = "ON UPDATE {$OnUpdate}";
        }

        return \implode(" ", $Field);

    }

}