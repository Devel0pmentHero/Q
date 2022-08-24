<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression;

use Q\Collation;
use Q\Expression\IAggregateFunction;
use Q\PgSQL\Provider;
use Q\Type;

/**
 * Utility class for table related PgSQL Expressions providing functionality for creating fields and indexes.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
abstract class Table extends \Q\AnsiSQL\Expression\Table {

    /**
     * Enumeration of supported PGSQL type mappings.
     */
    public const Collations = [
        Collation::ASCII                     => "en_US.utf8",
        Collation::ASCII | Collation::Binary => "en_US.utf8",
        Collation::UTF8                      => "en_US.utf8",
        Collation::UTF8 | Collation::Binary  => "en_US.utf8",
        Collation::UTF16                     => "en_US.utf8",
        Collation::UTF16 | Collation::Binary => "en_US.utf8",
        Collation::UTF32                     => "en_US.utf8",
        Collation::UTF32 | Collation::Binary => "en_US.utf8"
    ];

    /**
     * Enumeration of PGSQL specified type mappings.
     */
    public const Types = [
        Type::TinyInt    => "SMALLINT",
        Type::SmallInt   => "SMALLINT",
        Type::Int        => "INTEGER",
        Type::BigInt     => "BIGINT",
        //Sorry, but postgres' boolean sucks...
        Type::Boolean    => "SMALLINT",
        Type::Decimal    => "DECIMAL",
        Type::Float      => "REAL",
        Type::Double     => "DOUBLE PRECISION",
        Type::Char       => "CHAR",
        Type::VarChar    => "VARCHAR",
        Type::TinyText   => "VARCHAR(255)",
        Type::Text       => "VARCHAR(65535)",
        Type::MediumText => "VARCHAR(16777215)",
        Type::LongText   => "TEXT",
        Type::Timestamp  => "TIMESTAMP",
        Type::Date       => "DATE",
        Type::Time       => "TIME",
        Type::DateTime   => "TIMESTAMPTZ",
        Type::TinyBlob   => "BYTEA(255)",
        Type::Blob       => "BYTEA(65535)",
        Type::MediumBlob => "BYTEA(16777215)",
        Type::LongBlob   => "BYTEA(4294967295)"
    ];

    /**
     * Creates a PGSQL conform table field.
     *
     * @param string      $Name          The name of the table field.
     * @param int         $Type          The type of the table field.
     * @param bool        $Nullable      Flag indicating whether the table field is nullable.
     * @param string      $Default       The default value of the table field.
     * @param bool        $AutoIncrement The size of the table field.
     * @param string|null $OnUpdate      The size of the table field.
     *
     * @return string A PgSQL conform table field.
     */
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

        if($AutoIncrement) {
            //Map autoincrement flags to postgres' serial type.
            $Field[] = match (static::Types[$Type & ~Type::Unsigned]) {
                static::Types[Type::SmallInt], static::Types[Type::TinyInt] => "SMALLSERIAL",
                static::Types[Type::Int] => "SERIAL",
                static::Types[Type::BigInt] => "BIGSERIAL"
            };
        } else {
            $Field[] = static::Types[$Type & ~Type::Unsigned];
        }

        $Field[] = $Nullable ? Provider::NULL : "NOT " . Provider::NULL;

        if($Default !== "") {
            if($Default instanceof IAggregateFunction) {
                $Field[] = "DEFAULT " . \rtrim((string)$this->Provider->Sanitize($Default), "()");
            } else {
                $Field[] = "DEFAULT " . $this->Provider->Sanitize($Default);
            }
        }

        if($OnUpdate !== null) {
            $Field[] = "ON UPDATE {$OnUpdate}";
        }

        return \implode(" ", $Field);

    }

}