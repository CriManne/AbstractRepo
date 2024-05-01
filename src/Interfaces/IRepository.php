<?php

declare(strict_types=1);

namespace AbstractRepo\Interfaces;

/**
 * The interface that must be implemented by every repository that want to use the abstraction
 */
interface IRepository
{
    static function getModel(): string;
}