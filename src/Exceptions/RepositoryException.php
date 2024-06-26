<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;

use AbstractRepo\Repository\AbstractRepository;
use Exception;

/**
 * Repository exceptions that can be thrown by the {@see AbstractRepository}
 */
final class RepositoryException extends Exception
{
    /**
     * List of const for each custom exception.
     */
    public const string MODEL_MUST_IMPLEMENTS_INTERFACE        = 'The model must implement IModel interface';
    public const string MODEL_IS_NOT_ENTITY                    = 'The model has no entity attribute';
    public const string FETCH_BY_ID_MULTIPLE_RESULTS           = 'Retrieved more than one object when fetching by id!';
    public const string RELATED_OBJECT_NOT_FOUND               = 'Related object not found';
    public const string INVALID_PROMOTED_PROPERTY              = 'Invalid promoted property';
    public const string MODEL_IS_NOT_HANDLED                   = 'The model is not handled by the repository.';
    public const string ONE_TO_ONE_RELATIONSHIP_FAIL           = 'There cannot be multiple records with the same foreign key if the relationship is ONE TO ONE';
    public const string ONE_TO_MANY_FOREIGN_KEY_INVALID_TYPE   = 'The one to many foreign key field must be a nullable array initialized to null.';
    public const string ONE_TO_MANY_CANNOT_BE_PRIMARY_KEY      = 'The one to many foreign key cannot be set as primary key.';
    public const string CANNOT_FIND_WHERE_BY_ONE_TO_MANY_FIELD = 'Cannot find where by the one to many field';
    public const string CANNOT_FIND_PRIMARY_KEY_VALUE          = 'Cannot find primary key value.';
}