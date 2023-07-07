<?php

declare(strict_types=1);

namespace AbstractRepo\Util;

/**
 * Used to handle model fields
 */
final class ModelField{

    public string $fieldName;
    public ?string $fieldType;
    public $fieldValue;

    function __construct(string $fieldName,?string $fieldType,$fieldValue=null){

        $this->fieldName = $fieldName;
        $this->fieldType = $fieldType;
        $this->fieldValue = $fieldValue;

    }


}


?>
