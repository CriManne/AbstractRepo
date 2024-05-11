<?php

declare(strict_types=1);

namespace AbstractRepo\Enums;

use AbstractRepo\Attributes;

/**
 * Identifies the types of FOREIGN KEY relationships
 */
enum Relationship
{
    case MANY_TO_ONE;
    case ONE_TO_ONE;
    case ONE_TO_MANY;

    public static function fromAttribute(Attributes\ForeignKey $attribute): self
    {
        return match (true) {
            $attribute instanceof Attributes\ManyToOne => self::MANY_TO_ONE,
            $attribute instanceof Attributes\OneToMany => self::ONE_TO_MANY,
            $attribute instanceof Attributes\OneToOne  => self::ONE_TO_ONE
        };
    }
}