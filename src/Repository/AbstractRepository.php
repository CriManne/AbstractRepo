<?php

declare(strict_types=1);

namespace AbstractRepo\Repository;

use AbstractRepo\Attributes\Entity;
use AbstractRepo\Attributes\ForeignKey;
use AbstractRepo\Enums\Relationship;
use AbstractRepo\Exceptions\ORMException;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Interfaces\IModel;
use AbstractRepo\Interfaces\IRepository;
use AbstractRepo\Util\ModelField;
use AbstractRepo\Util\ORM;
use AbstractRepo\Util\ReflectionUtility;
use PDO;
use PDOException;
use PDOStatement;

/**
 * The abstract class from which extends the repository
 */
abstract class AbstractRepository{

    // The pdo object to use the database
    protected PDO $pdo;
    // The class of the model handled by the repository (ex: AbstractRepo\Models\Book)
    private $modelClass;
    // The name of the table in which the model is stored
    private string $tableName;

    function __construct(PDO $pdo){
        // If the repository doesn't implements IRepository it won't have the getModel method
        if(!$this instanceof IRepository) throw new RepositoryException(RepositoryException::$REPOSITORY_MUST_IMPLEMENTS);
        
        // Invoke the method to get the model handled by the repository (ex: Book)
        $this->modelClass = ReflectionUtility::invokeMethodOfClass(get_class($this),"getModel",null);      
        
        // Check if the class implements IModel
        if(!ReflectionUtility::class_implements($this->modelClass,IModel::class)) throw new RepositoryException(RepositoryException::$MODEL_MUST_IMPLEMENTS);

        // Check if the model handled has the Entity attribute
        $entityProperty = ReflectionUtility::getAttribute(ReflectionUtility::getReflectionClass($this->modelClass),Entity::class);
        
        // If there is not Entity attribute it will trigger a RepositoryException
        if(is_null($entityProperty)) throw new RepositoryException(RepositoryException::$MODEL_ISNT_ENTITY);

        // If there is no table name specified in the constructor of the Entity attribute 
        // it will take the name of the model class
        if(count($entityProperty->getArguments())==0){
            $this->tableName = strtolower(ReflectionUtility::getClassShortName($this->modelClass));
        }else{
            $this->tableName = $entityProperty->getArguments()[0];
        }

        // Assign the pdo obect        
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }

    #region Public methods
    
    /**
     * Entry function to findAll models
     *
     * @return array|null
     */
    public function findAll(): ?array{        
        
        $query = "SELECT * FROM ".$this->tableName;
        $stmt = $this->pdo->query($query);        
        $arr = $stmt->fetchAll(PDO::FETCH_CLASS);
        $mappedArr = [];

        foreach ($arr as $item) {
            $mappedArr[] = $this->getMappedObject((array)$item,$this->modelClass);
        }
        
        return $mappedArr;
    }

    /**
     * Entry function to find by id a Model
     *
     * @param [type] $id
     * @param string|null $model
     * @param string|null $table
     * @throws RepositoryException If it finds multiple results meaning database or entities are not configured properly
     * @return IModel|null
     *
     */
    public function findById($id,string $model = null,string $table = null): ?IModel{

        // Since this function will be called recursively to handle nesting and foreign keys, it could be
        // called with different modelClasses and tableNames
        $modelClass = $model ?? $this->modelClass;
        $tableName = $table ?? $this->tableName;

        // Get the key property of the model
        $keyProperty = ReflectionUtility::getKeyProperty($modelClass);
        
        // Get the name of the key
        $propertyName = $keyProperty->getName();
        
        $res = $this->findWhere($tableName,$modelClass,$propertyName,$id);

        if(count($res)==0) return null;

        if(count($res)>1) throw new RepositoryException(RepositoryException::$FETCH_BY_ID_MULTIPLE_RESULTS);

        return $res[0];
    }   
    
    /**
     * Make a filtered select with the parameter passed
     *
     * @param string $tableName
     * @param string $modelClass
     * @param string $property
     * @param [type] $value
     * @return array|null
     */
    private function findWhere(string $tableName,string $modelClass,string $property,$value):?array{
        
        $query = "SELECT * FROM $tableName WHERE ";
        // Get the key property of the model
        $property = ReflectionUtility::getProperty($modelClass,$property);
        
        // Get the name of the key
        $propertyName = $property->getName();

        // Get the PDO type of the property
        $idType = $this->getPDOType(strval($property->getType()));

        // Creates the where condition (ex: WHERE ID = :ID)
        $placeholder = ":$propertyName";
        $query .= "$propertyName = $placeholder";

        // Prepares, binds, executes and fetch the query
        $stmt = $this->pdo->prepare($query);                           
        $stmt->bindParam($placeholder,$value,$idType);        
        $stmt->execute();
        $arr = $stmt->fetchAll(PDO::FETCH_CLASS);

        $mappedArr = [];
        
        foreach ($arr as $item) {
            $mappedArr[] = $this->getMappedObject((array)$item,$modelClass);
        }
        
        return $mappedArr;
    }
    
    /**
     * Entry function to save a Model
     *
     * @param IModel $model
     * @throws RepositoryException If the database triggers an exception
     * @return void
     */
    public function save(IModel $model): void{
        try{
            $stmt = $this->getInsertStatement($model,$this->pdo);
            $stmt->execute();   
        }catch(PDOException $ex){
            throw new RepositoryException($ex->getMessage());
        }
    }

    /**
     * Entry function to update a Model
     *
     * @param IModel $model
     * @throws RepositoryException If the database triggers an exception
     * @return void
     */
    public function update(IModel $model): void{
        try{
            $stmt = $this->getUpdateStatement($model,$this->pdo);
            $stmt->execute();    
        }catch(PDOException $ex){
            throw new RepositoryException($ex->getMessage());
        }
    }

    /**
     * Entry function to delete a Model
     *
     * @param IModel $model
     * @throws RepositoryException If the database triggers an exception
     * @return void
     *
     */
    public function delete($id): void{
        try{
            $stmt = $this->getDeleteStatement($id,$this->pdo);
            $stmt->execute();    
        }catch(PDOException $ex){
            throw new RepositoryException($ex->getMessage());
        }
    }

    /**
     * Returns the instance model from the array gave by the database
     * It handles the foreign keys with the
     *
     * @param mixed $obj
     * @param string $modelClass
     * @throws RepositoryException If the related object is not found or if the orm mapping triggers an exception
     * @return ?IModel
     */
    public function getMappedObject(mixed $obj,string $modelClass) : ?IModel{
        if(!isset($obj) || $obj == null || !$obj) return null;
        
        $fkProperties = ReflectionUtility::getFkProperties($modelClass);       
            
        foreach($fkProperties as $fkProperty){
            $columnName = null;
            $fkAttribute = ReflectionUtility::getAttribute($fkProperty,ForeignKey::class);
            $arguments = $fkAttribute->getArguments();

            // If the column name is specified in the ForeignKey attribute use it
            if(count($arguments)<2){
                $columnName = strtolower($fkProperty->name);
            }else{
                $columnName = $arguments[1];
            }
            // Removes the id from the object
            $id = $obj[$columnName."_id"];
            unset($obj[$columnName."_id"]);            
            
            // Needs the full path of the class of the model (ex: AbstractRepo\Model\User)
            $reflClass = ReflectionUtility::getReflectionClass($modelClass);
            $prop = $reflClass->getProperty($fkProperty->name);            
            $fkClass = $prop->getType()->getName();

            $fkObj = $this->findById($id,$fkClass,$columnName);
            
            if(is_null($fkObj)) throw new RepositoryException(RepositoryException::$RELATED_OBJECT_NOT_FOUND);
                        
            $obj[$fkProperty->name] = $fkObj;
        }
        
        try{
            $mappedObj = ORM::getNewInstance($modelClass,(array)$obj);               
        }catch(ORMException $ex){
            throw new RepositoryException($ex->getMessage());
        }
        
        return $mappedObj;
    }

    #endregion

    #region Private methods

    /**
     * Returns the PDOStatement for the insert operation
     *
     * @param IModel $model
     * @param PDO $pdo
     * @throws RepositoryException If the model has no valid fields to take the data from
     * @return PDOStatement
     */
    private function getInsertStatement(IModel $model,PDO $pdo) : PDOStatement{
        $query = "INSERT INTO $this->tableName";

        // Get the values array to get each value from the model
        $values = $this->getValuesFromModel($model);
        
        if(count($values) == 0){
            throw new RepositoryException(RepositoryException::$NO_MODEL_DATA_FOUND);
        }
        
        // Get an array with just the columns names
        $columns = array_map(
            function(ModelField $val){
                return $val->fieldName;
            },
            $values
        );

        // Creates the second part of the query (ex: (col1,col2) VALUES ( :col1,:col2))
        $query.=" ( ".implode(",",$columns)." ) VALUES (".implode(",",array_map(function($val){ return ":$val";},$columns)).");";

        $stmt = $pdo->prepare($query);

        // For each placeholder (:colName) bind the param with its type
        $stmt = $this->bindValues($values,$stmt);

        return $stmt;
    }

    /**
     * Returns an array used in the insert an update operation to get every value of the object
     *
     * @param string $classModel
     * @throws RepositoryException If the related object is not found or if a specific field is required but is not set
     * @return ModelField[]
     */
    private function getValuesFromModel(IModel $model): array{

        $classModel = get_class($model);

        
        $reflectionClass = ReflectionUtility::getReflectionClass($classModel);

        $reflectionProperties = $reflectionClass->getProperties();
        
        $values = [];

        foreach ($reflectionProperties as $reflectionProperty) {
            // Flag to see if a property is identity so it doesn't need to be inserted
            $isIdentity = false;

            // Flag to check if is required
            $isRequired = false;

            // Flag to check if is fk and which type
            $typeOfFk = null;

            // If the column name of the fk is specified we store it
            $fkColumnName = null;

            // Attributes of the property
            $attributes = $reflectionProperty->getAttributes();
            
            // If the property has no attributes it will be skipped
            if(count($attributes)==0) continue;

            foreach ($attributes as $attribute) {
                $attributeName = $attribute->getName();                               
                
                // If is an identity we are not going to add it in the insert query
                if($attributeName == Key::class && $attribute->getArguments()[0] == true){
                    $isIdentity = true;
                }    
                
                // If is a key not identity or if is required we must insert it
                if(($attributeName == Key::class && $attribute->getArguments()[0] != true)
                        || $attributeName == Required::class
                    ){
                    $isRequired = true;
                }

                // We get the ENUM type of Relationship if it is a foreign key and the eventual column name
                if($attributeName == ForeignKey::class){  
                    $arguments = $attribute->getArguments();                  
                    $typeOfFk = Relationship::fromString($arguments[0]->name);  
                    if(count($arguments)>1){
                        $fkColumnName = $arguments[1];
                    }      
                }
            }

            // Get property name and type
            $propertyName = $reflectionProperty->getName();  
            $propertyType = strval($reflectionProperty->getType());
            
            // If it's not an identity
            if(!$isIdentity){

                // If is a fk
                if($typeOfFk != null){

                    // Handle the MANY_TO_ONE relation
                    if($typeOfFk == Relationship::MANY_TO_ONE || $typeOfFk == Relationship::ONE_TO_ONE){
                        // It takes the value of the fk from the model
                        $fkKeyPropertyRefl = ReflectionUtility::getKeyProperty($propertyType);                                                
                        $fkKeyProperty = $fkKeyPropertyRefl->name;                        
                        $value = $model->$propertyName->$fkKeyProperty;   

                        // Check if the ID is valid and therefore if there is a related record in the database                                             
                        $fkObj = $this->findById($value,$propertyType,$propertyName);
            
                        if(is_null($fkObj)) throw new RepositoryException(RepositoryException::$RELATED_OBJECT_NOT_FOUND);

                        $propertyType = strval($fkKeyPropertyRefl->getType());                                  
                        
                        // If the column name is specified in the ForeignKey attribute use it
                        if(!is_null($fkColumnName)){
                            $propertyName = $fkColumnName;
                        }

                        // Get the fk column
                        $propertyName .= "_id";
                    }

                }else{
                    // If it's not a fk just add the value
                    $value = $reflectionProperty->getValue($model);
                }                

                if($isRequired && (!isset($value) || is_null($value) || empty($value))){
                    throw new RepositoryException("$propertyName is required!");
                }
                
                if((!isset($value) || is_null($value) || empty($value))){
                    continue;
                }
                
                // Array to store all the information to create the insert
                $values[] = new ModelField($propertyName,$propertyType,$value);
            }
        }

        return $values;
    }

    /**
     * Returns the PDOStatement for the update operation
     *
     * @param IModel $model
     * @param PDO $pdo
     * @throws RepositoryException If the model has no valid fields to take the data from
     * @return PDOStatement
     */
    private function getUpdateStatement(IModel $model,PDO $pdo) : PDOStatement{       

        $query = "UPDATE $this->tableName SET ";

        // Get the values array to get each value from the model
        $values = $this->getValuesFromModel($model);
        
        if(count($values) == 0){
            throw new RepositoryException(RepositoryException::$NO_MODEL_DATA_FOUND);
        }
        
        // Get the update string (ex: col1 = :col1, col2 = :col2)
        $columns = array_map(
            function(ModelField $val){
                return $val->fieldName." = :".$val->fieldName;
            },
            $values
        );

        $query.=implode(",",$columns);
        
        $keyProp = ReflectionUtility::getKeyProperty($model::class);
        $keyPropName = $keyProp->name;
        $keyPropValue = $keyProp->getValue($model);

        $query.=" WHERE $keyPropName = $keyPropValue";

        $stmt = $pdo->prepare($query);

        // Bind values to the statement
        $stmt = $this->bindValues($values,$stmt);       

        return $stmt;
    }

    /**
     * Returns the PDOStatement for the delete operation
     *
     * @param IModel $model
     * @param PDO $pdo
     * @return PDOStatement
     */
    private function getDeleteStatement($id,PDO $pdo) : PDOStatement{       

        $query = "DELETE FROM $this->tableName WHERE ";
        
        $keyProp = ReflectionUtility::getKeyProperty($this->modelClass);
        $keyPropName = $keyProp->name;
        $keyPropValue = $id;

        $query.=" $keyPropName = $keyPropValue";
        
        $stmt = $pdo->prepare($query);

        return $stmt;
    }

    /**
     * Bind the values in the array passed to the statement received
     *
     * @param ModelField[] $values
     * @param PDOStatement $stmt
     * @return PDOStatement
     */
    private function bindValues(array $values,PDOStatement $stmt):PDOStatement{
        foreach($values as $val){
            
            $type = $this->getPDOType($val->fieldType);

            $placeholder = ":".$val->fieldName;
            $value = $val->fieldValue;            

            $stmt->bindValue($placeholder,$value,$type);
        }
        return $stmt;
    }

    /**
     * Returns the PDO type from the PHP type
     *
     * @param string $type
     * @return ?int
     */
    private function getPDOType(string $type): ?int{
        switch($type){
            case 'int':
            case '?int':
                return PDO::PARAM_INT;
            case '?string':
            case 'string':
                return PDO::PARAM_STR;
            default:
                return null;
        }
    }    

    #endregion
}