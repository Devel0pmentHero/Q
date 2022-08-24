<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression;

use Q\Expression\IAlter;
use Q\PgSQL\Provider;
use Q\Type;

/**
 * Represents a PgSQL compatible "ALTER" Expression.
 *
 * @package Q\PgSQL
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Alter extends Table implements IAlter {

    use \Q\AnsiSQL\Expression\Alter {
        \Q\AnsiSQL\Expression\Alter::Table as AnsiTable;
        \Q\AnsiSQL\Expression\Alter::Drop as AnsiDrop;
        \Q\AnsiSQL\Expression\Alter::__toString as toString;
    }

    /**
     * The table name of the Alter Expression.
     *
     * @var string
     */
    private string $Table = "";

    /**
     * The indexes of the Alter Expression
     *
     * @var array
     */
    private array $Indexes = [];

    /** @inheritDoc */
    public function Table(string $Name): static {
        $this->Table = $Name;
        return $this->AnsiTable($Name);
    }

    /** @inheritDoc */
    public function Rename(string $Name): static {
        $Path            = \explode(Provider::Separator, $Name);
        $this->Statement .= "RENAME TO {$this->Provider->EscapeField(\array_pop($Path))} ";
        return $this;
    }

    /** @inheritDoc */
    public function Add(array $Columns, array $Indexes = []): static {
        foreach($Columns as $Name => $Column) {
            $this->Statements[] = "ADD COLUMN " . $this->Field(
                    $Name,
                    $Column["Type"],
                    $Column["Nullable"] ?? false,
                    $Column["Autoincrement"] ?? false,
                    $Column["Default"] ?? "",
                    null,
                    null,
                    $Column["OnUpdate"] ?? null
                );
        }
        foreach($Indexes as $Name => $Index) {
            $this->Indexes[] = $this->Provider->Create()
                                              ->Index($Name, $Index["Unique"] ?? false)
                                              ->On($this->Table, $Index["Fields"]);
        }
        return $this;
    }

    /** @inheritDoc */
    public function Modify(array $Columns, array $Indexes = []): static {
        //@todo Blame PostgreSQL developers.
        foreach($Columns as $Name => $Column) {
            if(\is_array($Column)) {
                if($Column["Autoincrement"] ?? false) {
                    $Type = match (static::Types[$Column["Type"] & ~Type::Unsigned]) {
                        static::Types[Type::SmallInt], static::Types[Type::TinyInt] => "SMALLSERIAL",
                        static::Types[Type::Int] => "SERIAL",
                        static::Types[Type::BigInt] => "BIGSERIAL"
                    };
                } else {
                    $Type = static::Types[$Column["Type"] & ~\Q\Type::Unsigned];
                }
                $this->Statements[] = "ALTER COLUMN {$this->Provider->SanitizeField($Name)} TYPE {$Type}";
                if(isset($Column["Nullable"])) {
                    $this->Statements[] = "ALTER COLUMN {$this->Provider->SanitizeField($Name)}" . ($Column["Nullable"] ? " SET NOT NULL" : " DROP NOT NULL");
                }
                if(isset($Column["Default"])) {
                    $this->Statements[] = "ALTER COLUMN {$this->Provider->SanitizeField($Name)} SET DEFAULT {$this->Provider->Sanitize($Column["Default"])}";
                }
            } else {
                $this->Statements[] = "ALTER COLUMN {$this->Provider->SanitizeField($Name)} RENAME TO {$this->Provider->SanitizeField($Column)}";
            }
        }
        foreach($Indexes as $Old => $New) {
            $this->Indexes[] = "ALTER INDEX {$this->Provider->SanitizeField($Old)} RENAME TO {$this->Provider->SanitizeField($New)}";
        }
        return $this;
    }

    /** @inheritDoc */
    public function Drop(array $Columns, array $Indexes = []): static {
        foreach($Indexes as $Index) {
            $this->Indexes[] = $this->Provider->Drop()
                                              ->Index($Index)
                                              ->On($this->Table);
        }
        return $this->AnsiDrop($Columns);
    }

    /** @inheritDoc */
    public function __toString(): string {
        if(\count($this->Indexes) > 0) {
            return $this->toString() . "; " . \implode("; ", $this->Indexes);
        }
        return $this->toString();
    }

}