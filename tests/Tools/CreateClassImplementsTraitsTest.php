<?php

namespace DraculAid\PhpMocker\tests\Tools;

use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Tools\CreateClassImplementsTraits;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see CreateClassImplementsTraits::exe()
 *
 * @run php tests/run.php tests/Tools/CreateClassImplementsTraitsTest.php
 */
class CreateClassImplementsTraitsTest extends TestCase
{
    private string $traitName1;
    private string $traitName2;

    public function testOne(): void
    {
        $this->createTraits();

        $setClassName = '__test_class_trait_name_' . uniqid() . '___';
        $className = CreateClassImplementsTraits::exe($this->traitName1, $setClassName);
        self::assertEquals($className, $setClassName);
        self::assertEquals('111', $className::f1());

        $classScheme = ReflectionReader::exe($className);
        self::assertTrue(empty($classScheme->methods['222']));
    }

    public function testList(): void
    {
        $this->createTraits();

        $className = CreateClassImplementsTraits::exe([$this->traitName1, $this->traitName2]);

        self::assertEquals('111', $className::f1());
        self::assertEquals('222', $className::f2());
    }

    private function createTraits(): void
    {
        $this->traitName1 = '__test_trait_name_1_' . uniqid() . '___';
        $this->traitName2 = '__test_trait_name_2_' . uniqid() . '___';

        eval(<<<CODE
                trait {$this->traitName1} {
                    public static function f1()
                    {
                        return '111';
                    }
                }
                trait {$this->traitName2} {
                    public static function f2()
                    {
                        return '222';
                    }
                }
            CODE
        );
    }
}
