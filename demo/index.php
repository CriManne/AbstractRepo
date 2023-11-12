<?php
declare(strict_types=1);

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

use Demo\Book;
use Demo\BookRepository;

$dsn= "define-dsn-here";
$username = "define-username-here";
$password = "define-password-here";

$pdo = new PDO($dsn, $username, $password);

$bookRepo = new BookRepository($pdo);

$book = new Book(1,"Test book");

$bookRepo->save($book);

$bookRepo->findAll();

$bookRepo->findById(1);

$book->val = "Updated book value";

$bookRepo->update($book);

$bookRepo->delete(1);


