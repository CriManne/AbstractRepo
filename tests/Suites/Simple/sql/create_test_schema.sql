DROP DATABASE IF EXISTS abstract_repo_test;

CREATE DATABASE abstract_repo_test;

USE abstract_repo_test;

CREATE TABLE T1
(
    id int          not null AUTO_INCREMENT,
    v1 varchar(255) not null,
    v2 varchar(255) null,
    PRIMARY KEY (id)
);