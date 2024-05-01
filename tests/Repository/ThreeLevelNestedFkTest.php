<?php

declare(strict_types=1);

namespace AbstractRepo\Test\Repository;

use AbstractRepo\DataModels\FetchParams;
use AbstractRepo\Exceptions\RepositoryException;
use AbstractRepo\Test\MockData\Models\T1;
use AbstractRepo\Test\MockData\Models\T2;
use AbstractRepo\Test\MockData\Models\T3;
use AbstractRepo\Test\MockData\Models\T4;
use AbstractRepo\Test\MockData\Models\T5;
use ReflectionException;

class ThreeLevelNestedFkTest extends BaseTest
{
   public function testSaveAndFindByIdThreeLevelNesting(): void
   {
       $t3 = new T3('abc', 'val3');

       self::$t3Repo->save($t3);

       $t4 = new T4($t3, 'val4');

       self::$t4Repo->save($t4);

       $t5 = new T5($t4, 'val5');

       self::$t5Repo->save($t5);

       self::assertEquals('val3', self::$t5Repo->findById('abc')->t4->t3->v1);
   }
}