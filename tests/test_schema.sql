DROP DATABASE IF EXISTS bookrental_test;

CREATE DATABASE bookrental_test;

USE bookrental_test;

CREATE TABLE T1(
    id int not null AUTO_INCREMENT,
    v1 varchar(255) not null,
    primary key(id)
);

CREATE TABLE T2(
    id int not null AUTO_INCREMENT,
    v1 varchar(255) not null,
    t1_id int not null,
    primary key(id),
    constraint fk_t2_1 foreign key (t1_id) references t1(id)
);
