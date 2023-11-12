DROP DATABASE IF EXISTS abstract_repo_test;

CREATE DATABASE abstract_repo_test;

USE abstract_repo_test;

CREATE TABLE `t1`(
    id int not null AUTO_INCREMENT,
    v1 varchar(255) not null,
    PRIMARY KEY(id)
);

CREATE TABLE `t2`(
    id int not null AUTO_INCREMENT,
    v1 varchar(255) not null,
    t1_id int not null,
    primary key(id),
    constraint fk_t2_1 foreign key (t1_id) references `t1`(id)
);
