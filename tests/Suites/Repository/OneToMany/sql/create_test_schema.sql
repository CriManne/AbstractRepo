DROP
DATABASE IF EXISTS abstract_repo_test;

CREATE
DATABASE abstract_repo_test;

USE
abstract_repo_test;

CREATE TABLE T1
(
    id int          not null AUTO_INCREMENT,
    v1 varchar(255) not null,
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
    id int not null AUTO_INCREMENT,
    primary key (id)
);

CREATE TABLE T4
(
    t3_id int          not null,
    v1    VARCHAR(255) not null,
    primary key (t3_id),
    constraint fk_t3_1 foreign key (t3_id) references T3 (id)
);

CREATE TABLE T5
(
    id    int not null AUTO_INCREMENT,
    t4_id int not null,
    primary key (id),
    constraint fk_t4_1 foreign key (t4_id) references T4 (t3_id)
)