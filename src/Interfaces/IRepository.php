<?php

declare(strict_types=1);

namespace AbstractRepo\Interfaces;

/**
 * The interface that must be implemented by every repository that want to use the abstraction
 */
interface IRepository
{
    /**
     * Returns the name
     */
    public const string GET_MODEL_METHOD_NAME = 'getMode';

    static function getModel(): string;
}