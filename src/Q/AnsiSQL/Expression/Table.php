<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IProvider;

/**
 * Abstract baseclass for Expressions that work with tables.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
abstract class Table {

    /**
     * Initializes a new instance of the Table class.
     *
     * @param \Q\IProvider $Provider Initializes the Table Expression with the specified Provider.
     */
    public function __construct(protected IProvider $Provider) {
    }

    /**
     * Creates a table field statement compatible to the current target database.
     *
     * @param string      $Name          The name of the field.
     * @param int         $Type          The type of the field.
     * @param bool        $Nullable      Flag indicating whether the table field is nullable.
     * @param mixed       $Default       The default value of the table field.
     * @param bool        $AutoIncrement The size of the table field.
     * @param null|int    $Collation     The collation of the table field.
     * @param null|int    $Size          The size of the field.
     * @param null|string $OnUpdate      The size of the table field.
     *
     * @return string A table field statement compatible to the current target database.
     */
    abstract public function Field(
        string  $Name,
        int     $Type,
        bool    $Nullable = false,
        bool    $AutoIncrement = false,
        mixed   $Default = "",
        ?int    $Collation = null,
        ?int    $Size = null,
        ?string $OnUpdate = null
    ): string;

    /**
     * Creates an inline table index statement compatible to the current target database.
     *
     * @param string $Name   The name of the index.
     * @param array  $Fields The fields of the index.
     * @param bool   $Unique Flag indicating whether the index is unique.
     *
     * @return string An inline table index statement compatible to the current target database.
     */
    public function InlineIndex(string $Name, bool $Unique, array $Fields): string {

        if($Name === "Primary") {
            $Index = "PRIMARY KEY";
        } else if($Unique) {
            $Index = "UNIQUE INDEX {$Name}";
        } else {
            $Index = "INDEX {$Name}";
        }

        $Transformed = [];
        foreach($Fields as $Key => $Field) {
            $Transformed[] = \is_string($Key) ? $this->Provider->EscapeField($Key) : $this->Provider->EscapeField($Field);
        }

        return $Index . " (" . \implode(", ", $Transformed) . ")";
    }

}