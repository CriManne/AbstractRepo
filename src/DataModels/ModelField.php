<?php

declare(strict_types=1);

namespace AbstractRepo\DataModels;

/**
 * Used to handle model fields
 * @codeCoverageIgnore
 */
final readonly class ModelField
{
    public function __construct(
        public string              $fieldName,
        public ?string             $fieldType,
        public mixed               $fieldValue = null
    )
    {
    }
}
