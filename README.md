
# AbstractRepo


## About

This  project is a small, lightweight  library for abstracting repositories  and avoiding writing a lot of repository logic. It uses [Reflection](https://www.php.net/manual/en/book.reflection.php) on the generic and abstract class (*AbstractRepository*) that provides basic [CRUD](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete) methods.

### Methods available:
- *findAll* :   retrieves every record
- *findById* : retrieves a specific record
- *findWhere* : retrieves every record matching the where clause
- *save* : saves the model passed
- *update* : updates the model passed
- *delete* : deletes the model from the id
- *getMappedObject* : returns a new instance of the model passed as an array


## Installation

You can install this library using [composer](https://getcomposer.org/) with the
following command:

```
composer require crimanne/abstract-repo
```


## Usage

Basically, you need to define the ***Entity*** and the repository that will use it.

### Entity
You  need to define a class  which is the entity already created in the database and give it the ***Entity*** attribute. The model must implement the ***IModel*** interface, as shown below:
```
#[Entity]
class Foo implements IModel{

}
```
Then you need to define the fields that must match the ones in the database.
To define a primary key, you have to use the ***Key*** attribute on the field. The attribute accepts a boolean in the constructor indicating whether the primary key is ** auto increment**.
```
...
#[Key(true)]
int $ID;
...
```
To identify a normal field you must use the ***Required*** attribute if is not null or the ***NotRequired*** attribute if it is null. 
**Be aware that if a field has no valid attributes it will not be considered by the library.**
```
...
#[Required]
string $mustBeInserted;
#[NotRequired]
?string $canBeNull;
...
```
If you have a foreign key in the entity you can flag it with the ***ForeignKey*** attribute that accepts the ***Relationship*** enum: MANY_TO_ONE, ONE_TO_ONE ( the ONE_TO_MANY relation needs to me implemented ).
```
...
#[ForeignKey(Relationship::MANY_TO_ONE)]
RelatedModel $obj;
...
```

### Repository
After defining the entity, you have to create the repository that must extends the ***AbstractRepository*** class and implements the ***IRepository*** interface.
Then you need to define the ***getModel*** method to return the classname of the entity as follows:
```
class FooRepository extends AbstractRepository implements IRepository{
      
      ...
      static public function getModel():string{
	      return Foo::class;
      }
	  ...
	  
}
```
\
\
Finally you can use the methods of the library.
#

Copyright 2023 by [Cristian Mannella](http://www.cristianmannella.it)

Released under the [MIT License](LICENSE)
