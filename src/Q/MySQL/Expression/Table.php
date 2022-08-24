<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Collation;
use Q\MySQL\Provider;
use Q\Type;

/**
 * Utility class for table related MySQL Expressions providing functionality for creating/altering fields and indexes.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
abstract class Table extends \Q\AnsiSQL\Expression\Table {

    /**
     * The types of the Table.
     */
    public const Collations = [
        Collation::ASCII                     => "ascii_general_ci",
        Collation::ASCII | Collation::Binary => "ascii_bin",
        Collation::UTF8                      => "utf8mb4_unicode_ci",
        Collation::UTF8 | Collation::Binary  => "utf8mb4_bin",
        Collation::UTF16                     => "utf16_unicode_ci",
        Collation::UTF16 | Collation::Binary => "utf16_bin",
        Collation::UTF32                     => "utf32_unicode_ci",
        Collation::UTF32 | Collation::Binary => "utf32_bin"
    ];

    /**
     * The types of the Table.
     */
    public const Types = [
        Type::TinyInt    => "TINYINT",
        Type::SmallInt   => "SMALLINT",
        Type::Int        => "INT",
        Type::BigInt     => "BIGINT",
        Type::Boolean    => "TINYINT(1)",
        Type::Decimal    => "DECIMAL",
        Type::Float      => "FLOAT",
        Type::Double     => "DOUBLE",
        Type::Char       => "CHAR",
        Type::VarChar    => "VARCHAR",
        Type::TinyText   => "TINYTEXT",
        Type::Text       => "TEXT",
        Type::MediumText => "MEDIUMTEXT",
        Type::LongText   => "LONGTEXT",
        Type::Timestamp  => "TIMESTAMP",
        Type::Date       => "DATE",
        Type::Time       => "TIME",
        Type::DateTime   => "DATETIME",
        Type::TinyBlob   => "TINYBLOB",
        Type::Blob       => "BLOB",
        Type::MediumBlob => "MEDIUMBLOB",
        Type::LongBlob   => "LONGBLOB"
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

        //Create type and unsigned attribute.
        $Field[] = static::Types[$Type & ~Type::Unsigned]
                   . ($Size !== null ? "({$Size})" : "")
                   . (($Type & Type::Unsigned) || ($Type & Type::Boolean) ? " UNSIGNED" : "");

        //Create collation/charset.
        if($Collation !== null) {
            if($Collation & Collation::ASCII) {
                $Field[] = "CHARACTER SET ascii" . ($Collation & Collation::Binary ? " COLLATE ascii_bin" : "");
            } else {
                $Field[] = "COLLATE " . static::Collations[$Collation];
            }
        }

        $Field[] = $Nullable ? Provider::NULL : "NOT " . Provider::NULL;

        if($Default !== "") {
            $Field[] = "DEFAULT {$this->Provider->Sanitize($Default)}";
        }
        if($AutoIncrement) {
            $Field[] = "AUTO_INCREMENT";
        }
        if($OnUpdate !== null) {
            $Field[] = "ON UPDATE {$OnUpdate}";
        }

        return \implode(" ", $Field);

    }

    /** @inheritDoc */
    public function InlineIndex(string $Name, bool $Unique, array $Fields): string {

        if($Name === "Primary") {
            $Index = "PRIMARY KEY";
        } else if($Unique) {
            $Index = "UNIQUE INDEX {$Name}";
        } else {
            $Index = "INDEX {$Name}";
        }

        $Transformed = [];
        foreach($Fields as $Field => $Size) {
            if(\is_string($Field)) {
                $Transformed[] = $this->Provider->EscapeField($Field) . " ({$Size})";
            } else {
                $Transformed[] = $this->Provider->EscapeField($Size);
            }
        }

        return $Index . " (" . \implode(", ", $Transformed) . ")";
    }
}