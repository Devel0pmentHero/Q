<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Expression\IAlter;
use Q\MsSQL\Provider;

/**
 * Represents a MsSQL compatible "ALTER" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Alter extends Table implements IAlter {

    use \Q\AnsiSQL\Expression\Alter {
        Database as AnsiDatabase;
        Schema as AnsiSchema;
        Table as AnsiTable;
    }

    /**
     * The table name of the Alter Expression.
     *
     * @var string
     */
    protected string $Table = "";

    /**
     * Flag indicating whether the Expression alters a schema.
     *
     * @var bool
     */
    protected bool $Schema = false;

    /** @inheritDoc */
    public function Database(string $Name): static {
        $this->Table = $Name;
        return $this->AnsiDatabase($Name);
    }

    /** @inheritDoc */
    public function Schema(string $Name): static {
        $this->Schema = true;
        return $this->AnsiSchema($Name);
    }

    /** @inheritDoc */
    public function Table(string $Name): static {
        $this->Table = $Name;
        return $this->AnsiTable($Name);
    }

    /** @inheritDoc */
    public function Add(array $Columns, array $Indexes = []): static {
        foreach($Columns as $Name => $Column) {
            $this->Statements[] = (new static($this->Provider))->Table($this->Table)
                                                               ->AddColumn($Name, $Column);
        }
        foreach($Indexes as $Name => $Index) {
            $this->Statements[] = $this->Provider->Create()
                                                 ->Index($Name, $Index["Unique"] ?? false)
                                                 ->On($this->Table, $Index["Fields"]);
        }
        return $this;
    }

    /**
     * Applies an "ADD (COLUMN)" statement to the Expression.
     *
     * @param string $Name   The name of the column.
     * @param array  $Column The definition of the column.
     *
     * @return $this The current instance for further chaining.
     */
    protected function AddColumn(string $Name, array $Column): static {
        $this->Statement .= "ADD " . $this->Field(
                $Name,
                $Column["Type"],
                $Column["Nullable"] ?? false,
                $Column["Autoincrement"] ?? false,
                $Column["Default"] ?? "",
                $Column["Collation"] ?? null,
                $Column["Size"] ?? null,
                $Column["OnUpdate"] ?? null
            );
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function Rename(string $Name): static {
        if($this->Schema) {
            $this->Statements[] = "TRANSFER " . ($this?->Provider?->Database ?? "dbo") . Provider::Separator . $this->Provider->EscapeField($Name);
        } else {
            $this->Statements[] = "EXECUTE sp_rename {$this->Provider->Sanitize($this->Table)}, {$this->Provider->Sanitize($Name)}";
        }
        return $this;
    }

    /** @inheritDoc */
    public function Modify(array $Columns, array $Indexes = []): static {
        foreach($Columns as $Name => $Column) {
            if(\is_array($Column)) {
                $this->Statements[] = (new static($this->Provider))->Table($this->Table)
                                                                   ->AlterColumn($Name, $Column);
            } else {
                $this->Statements[] = (new static($this->Provider))->Table($this->Table)
                                                                   ->RenameColumn($Name, $Column);
            }
        }
        //Seriously Microsoft...
        foreach($Indexes as $Old => $New) {
            $this->Statements[] = (new static($this->Provider))->Table($this->Table)
                                                               ->RenameIndex($Old, $New);
        }
        return $this;
    }

    /**
     * Applies an "ALTER COLUMN" statement to the Expression.
     *
     * @param string $Name       The name of the column to alter.
     * @param array  $Definition The new column definition to set.
     *
     * @return $this The current instance for further chaining.
     */
    protected function AlterColumn(string $Name, array $Definition): static {
        //@todo How to preserve indices?!?
        $this->Statement .= "ALTER COLUMN " . $this->Field(
                $Name,
                $Definition["Type"],
                $Definition["Nullable"] ?? false,
                $Definition["Autoincrement"] ?? false,
                "",
                $Definition["Collation"] ?? null,
                $Definition["Size"] ?? null,
                $Definition["OnUpdate"] ?? null
            );
        if(isset($Definition["Default"])) {
            $this->Statements[] = $this->Statement;
            if($Definition["Default"] !== "") {
                $this->Statements[] = (new static($this->Provider))->Table($this->Table) . " ADD CONSTRAINT DF{$this->Provider->EscapeField($Name)}"
                                      . " DEFAULT " . $this->Provider->Sanitize($Definition["Default"])
                                      . " FOR " . $this->Provider->EscapeField($Name);
            } else {
                $this->Statements[] = (new static($this->Provider))->Table($this->Table) . " DROP CONSTRAINT DF{$this->Provider->EscapeField($Name)}";
            }
        }
        return $this;
    }

    /**
     * Calls the "sp_rename" stored procedure to rename a column.
     *
     * @param string $Old The name of the column to rename.
     * @param string $New The new name of the column.
     *
     * @return $this The current instance for further chaining.
     */
    protected function RenameColumn(string $Old, string $New): static {
        $this->Statement = "EXECUTE sp_rename {$this->Provider->Sanitize($this->Table . Provider::Separator . $Old)}, {$this->Provider->Sanitize($New)}, 'COLUMN'";
        return $this;
    }

    /**
     * Calls the "sp_rename" stored procedure to rename a index.
     *
     * @param string $Old The name of the column to rename.
     * @param string $New The new name of the column.
     *
     * @return $this The current instance for further chaining.
     */
    protected function RenameIndex(string $Old, string $New): static {
        $this->Statement = "EXECUTE sp_rename {$this->Provider->Sanitize($this->Table . Provider::Separator . $Old)}, {$this->Provider->Sanitize($New)}, 'INDEX'";
        return $this;
    }

    /** @inheritDoc */
    public function Drop(array $Columns, array $Indexes = []): static {
        $Drop = [];
        foreach($Indexes as $Index) {
            $Drop[] = $this->Provider->Drop()
                                     ->Index($Index)
                                     ->On($this->Table);
        }
        foreach($Columns as $Column) {
            $Drop[] = (new static($this->Provider))->Table($this->Table)
                                                   ->DropColumn($Column);
        }
        $this->Statements = [...$Drop, ...$this->Statements];
        return $this;
    }

    /**
     * Applies an "DROP (COLUMN)" statement to the Expression.
     *
     * @param string $Name The name of the column to drop.
     *
     * @return $this The current instance for further chaining.
     */
    protected function DropColumn(string $Name): static {
        $this->Statement .= "DROP COLUMN {$this->Provider->EscapeField($Name)}";
        return $this;
    }

    /** @inheritDoc */
    public function __toString(): string {
        if(\count($this->Statements) > 0) {
            return \implode("; \r\n\r\n", $this->Statements);
        }
        return $this->Statement;
    }

}