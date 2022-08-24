<?php
declare(strict_types=1);

namespace Q;

/**
 * Interface for database models.
 *
 * @package Q
 * @author  Kerry <Q@DevelopmentHero.de>
 */
interface IModel {

    /**
     * Gets the identifier of the IModel.
     *
     * @return mixed The ID of the IModel.
     */
    public function ID();

}