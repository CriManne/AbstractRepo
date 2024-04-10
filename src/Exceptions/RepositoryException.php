<?php

declare(strict_types=1);

namespace AbstractRepo\Exceptions;

use Exception;

/**
 * Triggered in repository operations
 */
final class RepositoryException extends Exception
{
    public const REPOSITORY_MUST_IMPLEMENTS = "The repository must implements IRepository interface";
    public const MODEL_MUST_IMPLEMENTS = "The model must implement IModel interface";
    public const MODEL_IS_NOT_ENTITY = "The model has no entity attribute";
    public const FETCH_BY_ID_MULTIPLE_RESULTS = "Retrieved more than one object when fetching by id!";
    public const RELATED_OBJECT_NOT_FOUND = "Related object not found";
    public const NO_MODEL_DATA_FOUND = "No bindable data found in the model fields";
}