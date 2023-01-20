<?php

namespace DraculAid\PhpMocker\tests\Creator\HardMocker\CreateClassExecuting;

use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see HardMocker::createClassExecuting() for abstract class {}
 *
 * @run php tests/run.php tests/Creator/HardMocker/CreateClassExecuting/AbstractClassTest.php
 */
class AbstractClassTest extends TestCase
{
    public function testRun(): void
    {
        $className = $this->getClassName();
        $phpCode = "abstract class {$className} {public static function f1(){return '111';} abstract public static function f_abstract();}";

        $classManager = MockCreator::hardFromPhpCode($phpCode);
        self::assertEquals('111', $className::f1());

        $classManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $className::f1());

        $classScheme = ReflectionReader::exe($className);
        self::assertArrayHasKey('f1', $classScheme->methods);
        self::assertArrayHasKey('f_abstract', $classScheme->methods);
        self::assertTrue($classScheme->methods['f_abstract']->isAbstract);
    }

    public function testAbstractRealisationWithoutMock(): void
    {
        $classNameAbstract = $this->getClassName();
        $classNameChild = $this->getClassName();
        $phpCodeForAbstractClass = "abstract class {$classNameAbstract} {public static function f1(){return '111';} abstract public static function f_abstract();}";
        $phpCodeForChildClass = "class {$classNameChild} extends {$classNameAbstract} {public static function f_abstract() {return 'abstract';}}";

        $classManagerAbstract = MockCreator::hardFromPhpCode($phpCodeForAbstractClass);
        eval($phpCodeForChildClass);

        self::assertEquals('111', $classNameChild::f1());
        self::assertEquals('abstract', $classNameChild::f_abstract());
    }

    public function testAbstractRealisationWithMock(): void
    {
        $classNameAbstract = $this->getClassName();
        $classNameChild = $this->getClassName();
        $phpCodeForAbstractClass = "abstract class {$classNameAbstract} {public static function f1(){return '111';} abstract public static function f_abstract();}";
        $phpCodeForChildClass = "class {$classNameChild} extends {$classNameAbstract} {public static function f_abstract() {return 'abstract';}}";

        $classManagerAbstract = MockCreator::hardFromPhpCode($phpCodeForAbstractClass);
        $classManagerChild = MockCreator::hardFromPhpCode($phpCodeForChildClass);

        self::assertEquals('111', $classNameChild::f1());
        self::assertEquals('abstract', $classNameChild::f_abstract());

        $classManagerAbstract->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $classNameChild::f1());

        $classManagerChild->getMethodManager('f_abstract')->defaultCase()->setWillReturn('abstract_AAA');
        self::assertEquals('abstract_AAA', $classNameChild::f_abstract());
    }

    private function getClassName(): string
    {
        return '___test_abstract_class_name_' . uniqid() . '___';
    }
}
