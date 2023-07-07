<?php

declare(strict_types=1);

namespace AbstractRepo\Attributes;

use AbstractRepo\Enums\Relationship;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
/**
 * Identifies a class as a relational entity
 */
final class Entity{

    public string $tableName;

    function __construct(string $tableName=null){
        $this->tableName = $tableName;
    }

}

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Identifies a (primary) key property of an entity
 */
final class Key{
    
    public bool $identity;

    function __construct(bool $identity=false){
        $this->identity = $identity;
    }

}


#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Identifies a required field
 */
final class Required{}

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Identifies a non required field
 */
final class NotRequired{}

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Identifies a foreign key field
 */
final class ForeignKey{
    public Relationship $relationType;
    public string $columnName;

    function __construct(Relationship $relation,string $columnName = null){
        $this->relationType = $relation;
        $this->columnName = $columnName;
    }
}
