<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Collation;
use Q\Expression\ICreate;

/**
 * Represents a MsSQL compatible "CREATE" Expression.
 *
 * @package Q\MsSQL\Expression
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Create extends Table implements ICreate {

    use \Q\AnsiSQL\Expression\Create;

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

        //Create indices.
        $Indices = [];
        foreach($Indexes as $IndexName => $Index) {
            if($IndexName === "Primary") {
                $Table[] = $this->InlineIndex($IndexName, $Index["Unique"] ?? false, $Index["Fields"]);
            } else {
                $Indices[] = (new static($this->Provider))->Index($IndexName, $Index["Unique"] ?? false)->On($Name, $Index["Fields"]);
            }
        }

        $this->Statement .= "TABLE {$this->Provider->SanitizeField($Name)} (" . \implode(", ", $Table) . "); ";
        $this->Statement .= \implode("; ", $Indices);
        return $this;
    }

    /** @inheritDoc */
    public function On(string $Table, array $Fields): static {
        $Transformed = [];
        foreach($Fields as $Field => $Size) {
            $Transformed[] = \is_string($Field) ? $this->Provider->EscapeField($Field) : $this->Provider->EscapeField($Size);
        }
        $this->Statement .= " ON {$this->Provider->SanitizeField($Table)} (" . \implode(", ", $Transformed) . ")";
        return $this;
    }

    /** @inheritDoc */
    public function Database(string $Name): static {
        $this->Statement .= "DATABASE {$Name} COLLATE " . Table::Collations[Collation::UTF8];
        return $this;
    }

}