<?php

declare(strict_types=1);

namespace AbstractRepo\Models;

use AbstractRepo\Models;
use AbstractRepo\Exceptions;

final class ModelsHandler
{

    /**
     * @param Models\ManagedModel[] $models
     */
    public function __construct(
        private array $models = []
    )
    {
    }

    /**
     * @param string $fullClassName
     * @return ManagedModel
     * @throws Exceptions\ReflectionException
     */
    private function getModel(string $fullClassName): Models\ManagedModel
    {
        $models = array_filter($this->models, function (Models\ManagedModel $model) use ($fullClassName) {
            return $model->getFullClassName() == $fullClassName;
        });

        if (count($models) == 0) throw new Exceptions\ReflectionException(Exceptions\ReflectionException::MANAGED_MODEL_NOT_FOUND);

        return $models[0];
    }

    /**
     * @param string $fullClassName
     * @param string $fieldName
     * @return FieldInfo
     * @throws Exceptions\ReflectionException
     */
    public function getField(string $fullClassName, string $fieldName): Models\FieldInfo
    {
        return $this->getModel($fullClassName)->getField($fieldName);
    }

    /**
     * @param string $fullClassName
     * @return Models\FieldInfo[]
     * @throws Exceptions\ReflectionException
     */
    private function getFks(string $fullClassName): array
    {
        return $this->getModel($fullClassName)->getFks();
    }

    public function registerModel(Models\ManagedModel $model): bool
    {
        try {
            $this->getModel($model->getFullClassName());
            return false;
        } catch (Exceptions\ReflectionException) {
            $this->models[] = $model;
            return true;
        }
    }
}