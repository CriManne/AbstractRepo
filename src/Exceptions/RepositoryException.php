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
    public const string MODEL_MUST_IMPLEMENTS_INTERFACE = 'The model must implement IModel interface';
    public const string MODEL_IS_NOT_ENTITY = 'The model has no entity attribute';
    public const string FETCH_BY_ID_MULTIPLE_RESULTS = 'Retrieved more than one object when fetching by id!';
    public const string RELATED_OBJECT_NOT_FOUND = 'Related object not found';
    public const string NO_MODEL_DATA_FOUND = 'No bindable data found in the model fields';
    public const string INVALID_PROMOTED_PROPERTY = 'Invalid promoted property';
    public const string MODEL_IS_NOT_HANDLED = 'The model is not handled by the repository.';
    public const string ONE_TO_ONE_RELATIONSHIP_FAIL = 'There cannot be multiple records with the same foreign key if the relationship is ONE TO ONE';
}