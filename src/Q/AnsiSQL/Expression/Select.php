<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\Expression\IAggregateFunction;
use Q\IProvider;
use Q\IResult;
use Q\Expression\ISelect;

/**
 * Traits for AnsiSQL compatible "SELECT" Expressions.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Select {

    use Where;

    /**
     * The SQL-statement of the Select.
     *
     * @var string
     */
    protected string $Statement = "";

    /**
     * The last join alias of the Select.
     *
     * @var string[]
     */
    protected array $Aliases = [];

    /** @inheritDoc */
    public function __construct(protected IProvider $Provider, string|array|IAggregateFunction ...$Fields) {

        $FlattenedFields = [];

        foreach($Fields as $Field) {
            //Check if an alias has been passed.
            if(\is_array($Field)) {
                //Check if an IAggregateFunction has been passed.
                if(isset($Field[0]) && $Field[0] instanceof IAggregateFunction) {
                    $FlattenedFields[] = (string)$Field[0] . "{$Field[0]} AS {$this->Provider->EscapeField($Field[1])}";
                    continue;
                }
                $FlattenedFields[] = $this->Provider->SanitizeField(\key($Field)) . " AS {$this->Provider->EscapeField(\current($Field))}";
                continue;
            }
            //Check if an IAggregateFunction has been passed.
            if($Field instanceof IAggregateFunction) {
                $FlattenedFields[] = (string)$Field;
                continue;
            }

            $FlattenedFields[] = $this->Provider->SanitizeField($Field);
        }

        $this->Statement .= "SELECT " . \implode(", ", $FlattenedFields) . (\count($FlattenedFields) > 0 ? " " : "");

    }

    /** @inheritDoc */
    public function Distinct(string|array|IAggregateFunction ...$Fields): static {
        $FlattenedFields = [];

        foreach($Fields as $Field) {
            $FlattenedFields[] = \is_array($Field)
                ? $this->Provider->SanitizeField(\key($Field)) . " AS " . $this->Provider->EscapeField(\current($Field))
                : $this->Provider->SanitizeField($Field);
        }

        $this->Statement .= "DISTINCT " . \implode(", ", $FlattenedFields) . " ";
        return $this;
    }

    /** @inheritDoc */
    public function From(string|array|ISelect ...$Tables): static {

        $FlattenedTables = [];

        foreach($Tables as $Table) {

            //Check if a sub select has been passed.
            if($Table instanceof ISelect) {
                $this->Aliases[] = $Alias = \next($Tables);
                $this->Statement .= "FROM ({$Table}) AS {$this->Provider->EscapeField($Alias)} ";
                return $this;
            }

            if(\is_array($Table)) {
                $this->Aliases[]   = $Alias = \current($Table);
                $FlattenedTables[] = "{$this->Provider->SanitizeField(\key($Table))} AS {$this->Provider->EscapeField($Alias)}";
                continue;
            }
            // Strip out any database names.
            $this->Aliases[]   = \substr($Table, \strrpos($Table, $this->Provider::Separator) + 1);
            $FlattenedTables[] = $this->Provider->SanitizeField($Table);
        }

        $this->Statement .= "FROM " . \implode(", ", $FlattenedTables) . " ";

        return $this;

    }

    /** @inheritDoc */
    public function Where(array ...$Conditions): static {
        $this->Statement .= "WHERE {$this->TransformConditions($this->Aliases, ...$Conditions)} ";
        return $this;
    }

    /** @inheritDoc */
    public function InnerJoin(string $Table, string $Alias = null): static {
        return $this->Join("INNER", $Table, $Alias);
    }

    /** @inheritDoc */
    public function RightJoin(string $Table, string $Alias = null): static {
        return $this->Join("RIGHT OUTER", $Table, $Alias);
    }

    /** @inheritDoc */
    public function LeftJoin(string $Table, string $Alias = null): static {
        return $this->Join("LEFT OUTER", $Table, $Alias);
    }

    /** @inheritDoc */
    public function FullJoin(string $Table, string $Alias = null): static {
        return $this->Join("FULL OUTER", $Table, $Alias);
    }

    /**
     * Applies a JOIN statement with a specified type.
     *
     * @param string      $Type  The type of the statement.
     * @param string      $Table The table to join.
     * @param null|string $Alias An optional alias for the table to join.
     *
     * @return $this The current instance for further chaining.
     */
    protected function Join(string $Type, string $Table, string $Alias = null): static {
        $this->Aliases[] = $Alias ?? \substr($Table, \strrpos($Table, $this->Provider::Separator) + 1);
        $this->Statement .= "{$Type} JOIN {$this->Provider->SanitizeField($Table)} " . ($Alias !== null ? "AS {$this->Provider->EscapeField($Alias)} " : "");
        return $this;
    }

    /** @inheritDoc */
    public function On(array ...$Fields): static {
        $this->Statement .= "ON {$this->TransformConditions($this->Aliases, ...$Fields)} ";
        return $this;
    }

    /** @inheritDoc */
    public function Limit(int $Amount): static {
        $this->Statement .= "LIMIT {$Amount} ";
        return $this;
    }

    /** @inheritDoc */
    public function Offset(int $Index): static {
        $this->Statement .= "OFFSET {$Index} ";
        return $this;
    }

    /** @inheritDoc */
    public function OrderBy(array $Fields): static {
        $Conditions = [];
        foreach($Fields as $Field => $Order) {
            if(\is_string($Order)) {
                $Conditions[] = $this->Provider->SanitizeField($Order);
            } else {
                $Conditions[] = $this->Provider->SanitizeField($Field) . " " . ((bool)$Order ? "ASC" : "DESC");
            }
        }
        $this->Statement .= "ORDER BY " . \implode(", ", $Conditions);
        return $this;
    }

    /** @inheritDoc */
    public function Union(ISelect $Select, bool $ALL = false): static {
        $this->Statement .= "UNION " . ($ALL ? "ALL " : "") . $Select;
        return $this;
    }

    /** @inheritDoc */
    public function Exists(ISelect $Select): static {
        $this->Statement .= "EXISTS ($Select)";
        return $this;
    }

    /** @inheritDoc */
    public function getIterator(): IResult {
        return $this->Execute();
    }

    //Implementation of IExpression.
    /** @inheritDoc */
    public function __invoke(): null|string|int|float {
        return $this->Execute()->ToValue();
    }

    /** @inheritDoc */
    public function Execute(bool $Buffered = true): IResult {
        return $this->Provider->Execute($this->Statement, $Buffered);
    }

    /** @inheritDoc */
    public function __toString(): string {
        return $this->Statement;
    }

}