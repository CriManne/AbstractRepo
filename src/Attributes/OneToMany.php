<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use Attribute;

/**
 * Relationship type ONE TO MANY
 * {@inheritDoc}
 * @codeCoverageIgnore
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly final class OneToMany extends ForeignKey
{
    public function __construct(
        /**
         * @var string $referencedColumn The column in the related entity.
         */
        public string $referencedColumn,

        /**
         * @var string $referencedClass The class name of the related entity.
         */
        public string $referencedClass,
    )
    {
    }
}
