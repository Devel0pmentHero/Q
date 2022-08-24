<?php
declare(strict_types=1);

namespace Q\AnsiSQL\Expression;

use Q\IModel;
use Q\IProvider;
use Q\Expression\IAggregateFunction;
use Q\Expression\Where as Condition;

/**
 * Trait for Expressions that implement "WHERE"- or "ON"-clauses.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
trait Where {

    /**
     * Initializes a new instance of the Where class.
     *
     * @param \Q\IProvider $Provider Initializes the Where Expression with the specified Provider.
     */
    public function __construct(protected IProvider $Provider) {
    }

    /**
     * Transforms sets of specified conditions into a SQL-conform format string.
     *
     * @param string[] $Aliases The aliases of the conditions.
     * @param array    ...$Conditions
     *
     * @return string A string containing the specified conditions in a SQL-conform format.
     */
    public function TransformConditions(array $Aliases = [], array ...$Conditions): string {

        $OrStatements = [];

        $Sanitize = function($Value) use ($Aliases) {
            //Check if the value is a referenced column.
            if(\is_string($Value)) {
                foreach($Aliases as $Alias) {
                    if(\str_contains(\trim($Value), $Alias . \Q::$Separator)) {
                        return $this->Provider->SanitizeField($Value);
                    }
                }
            }
            return $this->Provider->Sanitize($Value);
        };

        foreach($Conditions as $Condition) {
            $AndStatements = [];
            foreach($Condition as $Field => $Value) {
                if(\is_array($Value)) {
                    //Check if a set of nested statements has been passed.
                    if(\is_int($Field)) {
                        $AndStatements[] = $this->TransformConditions($Aliases, ...$Value);
                        continue;
                    }
                    $Field           = $this->Provider->SanitizeField($Field);
                    $AndStatements[] = match (\key($Value)) {
                        0 => "({$Field} = " . \implode(" OR {$Field} = ", \array_map($Sanitize, $Value)) . ")",
                        Condition::In => "{$Field} " . Condition::In . " (" . \implode(",", \array_map($Sanitize, $Value[Condition::In])) . ")",
                        Condition::NotIn => "{$Field} " . Condition::NotIn . " (" . \implode(",", \array_map($Sanitize, $Value[Condition::NotIn])) . ")",
                        Condition::Like => "{$Field} " . Condition::Like . " '{$Value[Condition::Like]}'",
                        Condition::Between => "({$Field} " . Condition::Between . " {$Sanitize($Value[Condition::Between][0])} AND {$Sanitize($Value[Condition::Between][1])})",
                        Condition::NotBetween => "({$Field} " . Condition::NotBetween . " {$Sanitize($Value[Condition::NotBetween][0])} AND {$Sanitize($Value[Condition::NotBetween][1])})",
                        Condition::Regex => "{$Field} " . Condition::Regex . " '{$Value[Condition::Regex]}'",
                        Condition::NotRegex => "{$Field} " . Condition::NotRegex . " '{$Value[Condition::NotRegex]}'",
                        default => "{$Field} " . \key($Value) . " " . $Sanitize(\current($Value))
                    };
                    continue;
                }
                $Field = $this->Provider->SanitizeField($Field);
                if($Value instanceof IAggregateFunction) {
                    $AndStatements[] = "{$Field} = {$Value}";
                    continue;
                }
                if($Value instanceof IModel) {
                    $AndStatements[] = "{$Field} = {$Value->ID()}";
                    continue;
                }
                $AndStatements[] = "{$Field} = {$Sanitize($Value)}";
            }
            $OrStatements[] = \count($AndStatements) > 1
                ? "(" . \implode(" AND ", $AndStatements) . ")"
                : \implode(" AND ", $AndStatements);
        }

        return \count($OrStatements) > 1
            ? "(" . \implode(" OR ", $OrStatements) . ")"
            : \implode(" OR ", $OrStatements);
    }

}