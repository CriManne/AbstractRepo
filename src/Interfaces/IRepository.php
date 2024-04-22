<?php

declare(strict_types=1);

namespace AbstractRepo\Interfaces;

/**
 * The interface that must be implemented by every repository that want to use the abstraction
 * {@internal}
 */
interface IRepository
{
    /**
     *  This method must be implemented by all the child repositories and it must return the class name of the
     *  handled object.
     * @return string
     */
    static function getModel(): string;
}