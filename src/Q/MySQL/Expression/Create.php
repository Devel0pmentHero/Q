<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Expression\ICreate;

/**
 * Represents a MySQL compatible "CREATE" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Create extends Table implements ICreate {

    use  \Q\AnsiSQL\Expression\Create {
        \Q\AnsiSQL\Expression\Create::Database as AnsiSchema;
    }

    /**
     * Flag indicating whether the Database method has been called.
     *
     * @var bool
     */
    private bool $Database = false;

    /** @inheritDoc */
    public function Database(string $Name): static {
        $this->Database = true;
        return $this;
    }

    /**
     * Applies a "DATABASE"-statement to the Create Expression due to lack of schema support of MySQL.
     */
    public function Schema(string $Name): static {
        return $this->AnsiSchema($Name);
    }

    /** @inheritDoc */
    public function Table(string $Name, array $Fields = [], array $Indexes = [], $Options = []): static {
        $Table = [];
        foreach($Fields as $FieldName => $Field) {
            $Table[] = $this->Field(
                $FieldName,
                $Field["Type"],
                $Field["Nullable"] ?? false,
                $Field["Autoincrement"] ?? false,
                $Field["Default"] ?? "",
                $Field["Collation"] ?? null,
                $Field["Size"] ?? null,
                $Field["OnUpdate"] ?? null
            );
        }
        foreach($Indexes as $IndexName => $Index) {
            $Table[] = $this->InlineIndex($IndexName, $Index["Unique"] ?? false, $Index["Fields"]);
        }
        $this->Statement .= "TABLE {$this->Provider->SanitizeField($Name)} (" . \implode(", ", $Table) . ")";
        $this->Engine($Options["Engine"] ?? "INNODB");
        $this->Statement .= " DEFAULT CHARSET=" . ($Options["Charset"] ?? "utf8mb4");
        $this->Statement .= " COLLATE=" . ($Options["Collation"] ?? "utf8mb4_unicode_ci");
        return $this;
    }

    /**
     * Mysql specific extension for defining storage engines.
     *
     * @param string $Name The name of the storage engine to set.
     *
     * @return \Q\MySQL\Expression\Create The current instance for further chaining.
     */
    public function Engine(string $Name): static {
        $this->Statement .= " ENGINE=$Name";
        return $this;
    }

    /** @inheritDoc */
    public function Execute(bool $Buffered = true): \Q\IResult {
        if($this->Database) {
            return new \Q\Result(true);
        }
        return $this->Provider->Execute($this->Statement, $Buffered);
    }

}