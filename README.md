
# AbstractRepo


## About

This  project is a small, lightweight  library for abstracting repositories  and avoiding writing a lot of repository logic. 
It uses [Reflection](https://www.php.net/manual/en/book.reflection.php) and [PHP 8 Attributes](https://www.php.net/manual/en/language.attributes.overview.php) on the abstract class (*AbstractRepository*) that provides basic [CRUD](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete) methods.

### Methods available:
- *find* :   retrieves every record
- *findFirst* :   retrieves the first record
- *findByQuery* :   retrieves every record that matches the given query
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
You  need to define a class which is the entity already created in the database and give it the ***Entity*** attribute. 
The Entity attributes requires the name of the database table as parameter.
The model must implement the ***IModel*** interface, as shown below:

E.g.
```
#[Entity(tableName: 'Foo')]
class Foo implements IModel{

}
```
Then you need to define the fields that must match the ones in the database.

### Primary Key
To define a primary key, you have to use the ***PrimaryKey*** attribute on the field. 
The attribute accepts a boolean in the constructor indicating whether the primary key is **auto increment**.

E.g.
```
...
#[PrimaryKey(autoIncrement: true)]
public int $id,
...
```

### Foreign Key (ManyToOne, OneToOne, OneToMany)
When working with a foreign key in an entity, you can specify one of the following attributes:
- ManyToOne: This attribute enables you to have a child object directly within the parent class.
- OneToOne: Similar to ManyToOne, this attribute adds a constraint check when attempting to insert another
record with the same foreign key.
- OneToMany: This attribute allows you to specify an array containing all the related entity IDs when fetching the data.

Both ManyToOne and OneToOne attributes require the **columnName** parameter to be provided in the constructor. 
This parameter is used to map the foreign key with the column name in the database.

E.g.

```
...
#[ManyToOne(columnName: 'bar_id')
public Bar $bar,
...
```

To have a OneToMany relation you need to declare a nullable array, this will contain all the related ids.
The attribute requires two params:
- referencedColumn: the database column name in the referenced table.
- referenceClass: the referenced class

In the example you can see a OneToMany relationship between Author and Book.
In the table book there must be the column author_id in order to complete the relationship.

```
#[Entity('Author')
class Author implements IModel
    ...
    #[OneToMany(
        referencedColumn: 'author_id',
        referencedClass: Book::class
    )]
    public ?array $books = null
    ...
```
### Searchable
The repository offers an utility method called **findByQuery** that will accept a query search term, and it will
retrieve all the records that match (even partially) that query.
In order to do that you need to specify in the model which fields can be included in the
research by using the attribute **Searchable**.
If the attribute is put on a OneToOne or ManyToOne relationship property, the method will also
look for all the searchable fields in the related model.
Please note that this process will get only the searchable fields of the direct related entity, and
it will not go further than one level of nesting.

E.g.
```
...
#[Searchable]
public string $field,
...
```

### Repository
After defining the entity, you have to create the repository that must extends the ***AbstractRepository*** class.
Then you need to define the ***getModel*** method to return the classname of the entity that you want to handle in that repository.

E.g.
```
class FooRepository extends AbstractRepository implements IRepository{
      
      ...
      static public function getModel():string{
	      return Foo::class;
      }
	  ...
	  
}
```
The repository needs a PDO instance in construction, this can also be done with [Dependency Injection](https://php-di.org/doc/understanding-di.html).


## Demo

A small demo project can be found in the ***[demo/](demo/)*** folder

#

Copyright 2024 by [Cristian Mannella](https://cristianmannella.vercel.app/)

[AbstractRepo](https://github.com/CriManne/AbstractRepo) by [Cristian Mannella](https://cristianmannella.vercel.app/) is licensed under [Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International](https://creativecommons.org/licenses/by-nc-nd/4.0/?ref=chooser-v1) 

![CC](https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1) ![BY](https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1) ![NC](https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1) ![ND](https://mirrors.creativecommons.org/presskit/icons/nd.svg?ref=chooser-v1)
 