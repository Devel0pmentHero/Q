<?php
declare(strict_types=1);

namespace Q\PgSQL\Expression;

use Q\Expression\IInsert;
use Q\PgSQL\Provider;

/**
 * Represents a PgSQL compatible "INSERT" Expression.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
class Insert implements IInsert {

    use \Q\AnsiSQL\Expression\Insert {
        \Q\AnsiSQL\Expression\Insert::Values as AnsiValues;
    }

    /**
     * @inheritDoc
     */
    public function Values(array $Values, array ...$Multiple): static {
        //Assume the first field is an identity field.
        $Fields   = $this->Fields ?? \array_keys($Values);
        $Identity = \current($Fields);
        if(\current($Values) === null && \str_ends_with($Identity, "ID")) {
            if(($this->Fields[0] ?? null) === $Identity) {
                $Values[0] = Provider::Default;
            } else {
                $Values[$Identity] = Provider::Default;
            }
            foreach($Multiple as $Index => $MultipleValues) {
                if($MultipleValues[0] === null) {
                    $Multiple[$Index][0] = Provider::Default;
                }
            }
        }
        return $this->AnsiValues($Values, ...$Multiple);
    }
}