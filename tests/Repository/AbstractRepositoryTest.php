<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\Models\T1;
use AbstractRepo\Test\Models\T2;
use PHPUnit\Framework\TestCase;
use PDO;

class AbstractRepositoryTest extends TestCase{

    public static string $dsnTest = "define-here-test-dsn";    
    public static string $username = "define-here-test-username";
    public static string $password = "define-here-test-password";

    public static PDO $pdo;

    public static T1Repository $t1Repo;
    public static T2Repository $t2Repo;

    public static function setUpBeforeClass():void
    {
        self::$pdo = new PDO(self::$dsnTest,self::$username,self::$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>FALSE]);        
        self::$pdo->exec(file_get_contents('./tests/test_schema.sql'));
        self::$t1Repo = new T1Repository(self::$pdo);
        self::$t2Repo = new T2Repository(self::$pdo);
    }

    public function setUp(): void
    {
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        self::$pdo->exec("TRUNCATE TABLE t2;");
        self::$pdo->exec("TRUNCATE TABLE t1;");
        self::$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function testInvalidModel():void{   
        $this->expectException(RepositoryException::class);     
        
        new TestInvalidModelRepository(self::$pdo);        
    }

    public function testValidModelSaveAndFindById():void{
        $t1 = new T1(1,"test");
        self::$t1Repo->save($t1);
        $this->assertEquals(self::$t1Repo->findById(1)->v1,'test');
    }

    public function testValidModelSaveAndFindByIdWrongId():void{        
        $this->assertEquals(self::$t1Repo->findById(999),null);
    }

    public function testValidModelSaveAndFindAll():void{
        for($i = 100; $i<150; $i++){
            $t = new T1($i,"test");
            self::$t1Repo->save($t);
        }
        $this->assertEquals(count(self::$t1Repo->findAll()),50);
    }

    public function testValidModelUpdateAndFindById():void{
        $t1 = new T1(1,"test2");

        self::$t1Repo->save($t1);

        $t1->v1 = "test99";

        self::$t1Repo->update($t1);
        
        $this->assertEquals(self::$t1Repo->findById(1)->v1,'test99');
    }

    public function testValidModelUpdateAndFindByIdWrongId():void{
        $t1 = new T1(1,"test99");

        self::$t1Repo->save($t1);

        $t1->id = 999;
        $t1->v1 = "test99";

        self::$t1Repo->update($t1);

        $this->assertEquals(self::$t1Repo->findById(999),null);
        $this->assertNotEquals(self::$t1Repo->findById(1),null);
    }

    public function testValidModelDeleteAndFindById():void{
        $t1 = new T1(2,"test2");
        self::$t1Repo->save($t1);
        $this->assertNotEquals(self::$t1Repo->findById($t1->id),null);
        self::$t1Repo->delete($t1->id);
        $this->assertEquals(self::$t1Repo->findById($t1->id),null);
    }

    public function testValidRelationalModelSave():void{
        $t1 = new T1(1,"testRelation");
        self::$t1Repo->save($t1);
        $t2 = new T2(1,"test2",$t1);
        self::$t2Repo->save($t2);   

        $this->assertNotNull(self::$t2Repo->findById(1));
        $this->assertEquals(self::$t2Repo->findById(1)->t1->v1,"testRelation");
    }
    
    public function testInvalidRelationalModelSave():void{
        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
        $t1 = new T1(9999,"test");
        $t2 = new T2(2,"test2",$t1);
        self::$t2Repo->save($t2);   
    }

    public function testValidRelationalModelDelete():void{
        $t1 = new T1(1,"testRelation");
        self::$t1Repo->save($t1);
        $t2 = new T2(1,"test2",$t1);
        self::$t2Repo->save($t2);

        self::$t2Repo->delete(1);   
        $this->assertNull(self::$t2Repo->findById(1));
    }

    public function testInValidRelationalModelDelete():void{
        $this->expectException(RepositoryException::class);
        $t1 = new T1(1,"test");
        $t2 = new T2(2,"test2",$t1);
        self::$t1Repo->save($t1);
        self::$t2Repo->save($t2); 
        self::$t1Repo->delete(1);
    }

    public function testValidRelationalModelUpdate():void{
        $t1 = new T1(1,"testRelation");
        self::$t1Repo->save($t1);
        $t2 = new T2(1,"test2",$t1);
        self::$t2Repo->save($t2);

        $t2->v1 = "testUpdate";

        self::$t2Repo->update($t2);
        
        $this->assertEquals(self::$t2Repo->findById(1)->v1,"testUpdate");
        $this->assertEquals(self::$t2Repo->findById(1)->t1->v1,"testRelation");
    }

    public function testInvalidRelationalModelUpdate():void{        
        $this->expectExceptionMessage(RepositoryException::RELATED_OBJECT_NOT_FOUND);
        $t1 = new T1(99,"test");
        $t2 = new T2(4,"test2",$t1);

        self::$t2Repo->update($t2);
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo->exec(file_get_contents('./tests/drop_test_schema.sql'));
    }
}