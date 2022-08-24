<?php
declare(strict_types=1);

namespace Q\MySQL\Expression;

use Q\Expression\IAlter;

/**
 * Represents a MySQL compatible "ALTER" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Alter extends Table implements IAlter {

    use \Q\AnsiSQL\Expression\Alter;

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

    /** @inheritDoc */
    public function Schema(string $Name): static {
        $this->Statement .= "DATABASE {$this->Provider->SanitizeField($Name)} ";
        return $this;
    }

    /** @inheritDoc */
    public function Rename(string $Name): static {
        $this->Statements[] = "RENAME {$this->Provider->SanitizeField($Name)}";
        return $this;
    }

    /**
     * Applies a storage engine to the Alter.
     *
     * @param string $Name The name of the storage engine to set.
     *
     * @return \Q\MySQL\Expression\Alter The current instance for further chaining.
     */
    public function Engine(string $Name): static {
        $this->Statement .= " ENGINE=$Name";
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
                    $Column["Collation"] ?? null,
                    $Column["Size"] ?? null,
                    $Column["OnUpdate"] ?? null
                );
        }
        foreach($Indexes as $Name => $Index) {
            $this->Statements[] = "ADD {$this->InlineIndex($Name, $Index["Unique"] ?? false, $Index["Fields"])}";
        }
        return $this;
    }

    /** @inheritDoc */
    public function Modify(array $Columns, array $Indexes = []): static {
        foreach($Columns as $Name => $Column) {
            if(\is_array($Column)) {
                $this->Statements[] = "MODIFY COLUMN " . $this->Field(
                        $Name,
                        $Column["Type"],
                        $Column["Nullable"] ?? false,
                        $Column["Autoincrement"] ?? false,
                        $Column["Default"] ?? "",
                        $Column["Collation"] ?? null,
                        $Column["Size"] ?? null,
                        $Column["OnUpdate"] ?? null
                    );
            } else {
                $this->Statements[] = "RENAME COLUMN {$this->Provider->SanitizeField($Name)} TO {$this->Provider->SanitizeField($Column)}";
            }
        }
        foreach($Indexes as $Old => $New) {
            $this->Statements[] = "RENAME INDEX {$this->Provider->SanitizeField($Old)} TO {$this->Provider->SanitizeField($New)}";
        }
        return $this;
    }

    /** @inheritDoc */
    public function Execute(bool $Buffered = true): \Q\IResult {
        if($this->Database) {
            return new \Q\Result(true);
        }
        return $this->Provider->Execute((string)$this, $Buffered);
    }

}