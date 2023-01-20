<?php

namespace DraculAid\PhpMocker\tests\Creator\HardMocker\CreateClassExecuting;

use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\MockCreator;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see HardMocker::createClassExecuting() for class {}
 *
 * @run php tests/run.php tests/Creator/HardMocker/CreateClassExecuting/ClassTest.php
 */
class ClassTest extends TestCase
{
    public function testCreateFinalClass(): void
    {
        $className = $this->getClassName();
        $phpCode = "final class {$className} {public static function f1(){return '111';}}";

        $classManager = MockCreator::hardFromPhpCode($phpCode);
        self::assertEquals('111', $classManager->toClass::f1());

        $classManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $classManager->toClass::f1());
    }

    public function testCreateClassAndCallFinalAndPrivateMethods(): void
    {
        $className = $this->getClassName();
        $phpCode = "class {$className} {final public static function f1(){return '111';}}";

        $classManager = MockCreator::hardFromPhpCode($phpCode);
        self::assertEquals('111', $classManager->toClass::f1());

        $classManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $classManager->toClass::f1());

        // * * *

        $className = $this->getClassName();
        $phpCode = "class {$className} {private static function f1(){return '111';}}";

        $classManager = MockCreator::hardFromPhpCode($phpCode);
        self::assertEquals('111', $classManager->callMethod('f1'));

        $classManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $classManager->callMethod('f1'));
    }

    public function testCreateObject(): void
    {
        $className = $this->getClassName();
        $phpCode = "class {$className} {public function __construct(public string \$construct_var = 'XXX') {} public function f1(){return '111';}}";

        $classManager = MockCreator::hardFromPhpCode($phpCode);
        $objectManager1 = $classManager->createObjectAndManager(['ZZZ']);

        self::assertEquals('ZZZ', $objectManager1->toObject->construct_var);
        self::assertEquals('111', $objectManager1->toObject->f1());

        $classManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $objectManager1->toObject->f1());

        $objectManager1->getMethodManager('f1')->defaultCase()->setWillReturn('BBB');
        self::assertEquals('BBB', $objectManager1->toObject->f1());

        $objectManager2 = $classManager->createObjectAndManager();
        self::assertEquals('AAA', $objectManager2->toObject->f1());
    }

    public function testMockMethodInheritance(): void
    {
        $classNameParent = $this->getClassName();
        $classNameChild = $this->getClassName();
        $phpCodeForParentClass = "class {$classNameParent} {public static function f1(){return '111';}}";
        $phpCodeForChildClass = "class {$classNameChild} extends {$classNameParent} {public static function f1(){return parent::f1() . '222';}}";

        $classManagerParent = MockCreator::hardFromPhpCode($phpCodeForParentClass);
        $classManagerChild = MockCreator::hardFromPhpCode($phpCodeForChildClass);

        self::assertEquals('111', $classNameParent::f1());
        self::assertEquals('111222', $classNameChild::f1());

        $classManagerParent->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $classNameParent::f1());
        self::assertEquals('AAA222', $classNameChild::f1());

        $classManagerChild->getMethodManager('f1')->defaultCase()->setWillReturn('BBB');
        self::assertEquals('AAA', $classNameParent::f1());
        self::assertEquals('BBB', $classNameChild::f1());
    }

    private function getClassName(): string
    {
        return '___test_class_name_' . uniqid() . '___';
    }
}
