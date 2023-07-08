<?php

use Demo\Models\Book;
use Demo\Repository\BookRepository;

declare(strict_types=1);

$dsn= "define-dsn-here";
$username = "define-username-here";
$password = "define-password-here";

$pdo = new PDO($dsn,$username,$password);

$bookRepo = new BookRepository($pdo);

$book = new Book(1,"Test book");

$bookRepo->save($book);

$bookRepo->findAll();

$bookRepo->findById(1);

$book->val = "Updated book value";

$bookRepo->update($book);

$bookRepo->delete(1);

?>

