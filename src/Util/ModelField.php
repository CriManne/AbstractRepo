<?php

declare(strict_types=1);

namespace AbstractRepo\Util;

/**
 * Used to handle model fields
 */
final class ModelField
{
    public function __construct(
        public string  $fieldName,
        public ?string $fieldType,
        public mixed   $fieldValue = null)
    {
    }
}
