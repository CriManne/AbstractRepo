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

CREATE TABLE T2
(
    id    int          not null AUTO_INCREMENT,
    v1    varchar(255) not null,
    t1_id int          not null,
    primary key (id),
    constraint fk_t2_1 foreign key (t1_id) references T1 (id)
);

CREATE TABLE T3
(
    id    int          not null AUTO_INCREMENT,
    v1    varchar(255) not null,
    t2_id int          not null,
    primary key (id),
    constraint fk_t3_1 foreign key (t2_id) references T2 (id)
);