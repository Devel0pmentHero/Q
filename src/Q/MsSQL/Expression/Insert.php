<?php
declare(strict_types=1);

namespace Q\MsSQL\Expression;

use Q\Expression\IInsert;

/**
 * Represents a MsSQL compatible "INSERT" Expression.
 *
 * @package Q\MsSQL\Expression
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Insert implements IInsert {

    use \Q\AnsiSQL\Expression\Insert {
        Values as AnsiValues;
    }

    /** @inheritDoc */
    public function Values(array $Values, array ...$Multiple): static {
        //Assume the first field is an identity field.
        $Fields   = $this->Fields ?? \array_keys($Values);
        $Identity = \current($Fields);
        if(\current($Values) === null && \str_ends_with($Identity, "ID")) {
            //Omit null values.
            if(($this->Fields[0] ?? null) === $Identity) {
                unset($this->Fields[0], $Values[0]);
            } else {
                unset($Values[$Identity]);
            }
            foreach($Multiple as $Index => $MultipleValues) {
                if($MultipleValues[0] === null) {
                    unset($Multiple[$Index][0]);
                }
            }
        }
        return $this->AnsiValues($Values, ...$Multiple);
    }

}