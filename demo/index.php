<?php
declare(strict_types=1);

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use Demo\Book;
use Demo\Author;
use Demo\BookRepository;
use Demo\AuthorRepository;

$dsn= getenv('DB_DSN');
$username = getenv('DB_USERNAME');
$password = getenv('DB_PASSWORD');

$pdo = new PDO($dsn, $username, $password);

$pdo->exec("
        DROP TABLE IF EXISTS book;
        DROP TABLE IF EXISTS author;

        CREATE TABLE author(id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, val VARCHAR(255) NOT NULL);
        CREATE TABLE book(id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, val VARCHAR(255) NOT NULL, author_id INT NOT NULL, FOREIGN KEY (author_id) REFERENCES author (id));"
);

$bookRepo = new BookRepository($pdo);
$authRepo = new AuthorRepository($pdo);

$author = new Author(12, "Franco");

$book = new Book(
    val: "Book1",
    author: $author
);

$authRepo->save($author);

$bookRepo->save($book);

$bookRepo->find();

$bookRepo->findById(1);

$book->val = "Updated book value";

$bookRepo->update($book);

$bookRepo->delete(1);

$author->val ="Updated author value";

$authRepo->update($author);

$bookRepo->find();

$bookRepo->findById(1);