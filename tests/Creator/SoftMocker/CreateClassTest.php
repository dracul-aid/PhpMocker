<?php

namespace DraculAid\PhpMocker\tests\Creator\SoftMocker;

use DraculAid\PhpMocker\Creator\MockClassInterfaces\MockClassInterface;
use DraculAid\PhpMocker\Creator\MockClassInterfaces\SoftMockClassInterface;
use DraculAid\PhpMocker\Creator\SoftMocker;
use DraculAid\PhpMocker\Exceptions\Creator\SoftMockClassCreatorClassIsFinalException;
use DraculAid\PhpMocker\Exceptions\Creator\SoftMockClassCreatorClassIsNotClassException;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see SoftMocker::createClass()
 *
 * @run php tests/run.php tests/Creator/SoftMocker/CreateClassTest.php
 */
class CreateClassTest extends TestCase
{
    public function testInterfaces(): void
    {
        $className = "___test_class_" . uniqid() . '___';
        eval("class {$className} {public function f1(){return '111';}}");

        $mockClassManager = SoftMocker::createClass($className);
        $interfaces = class_implements($mockClassManager->toClass);

        self::assertArrayHasKey(SoftMockClassInterface::class, $interfaces);
        self::assertArrayHasKey(MockClassInterface::class, $interfaces);
    }

    public function testCreateForClass(): void
    {
        $className = "___test_class_" . uniqid() . '___';
        eval("class {$className} {public function f1(){return '111';}}");

        $mockClassManager = SoftMocker::createClass($className);
        self::assertTrue($mockClassManager->toClass !== $className);
        self::assertTrue(is_subclass_of($mockClassManager->toClass, $className));

        $mockObjectManager = $mockClassManager->createObjectAndManager();
        self::assertEquals('111', $mockObjectManager->toObject->f1());
        $mockObjectManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $mockObjectManager->toObject->f1());
    }

    public function testCreateForAbstractClass(): void
    {
        $className = "___test_abstract_class_" . uniqid() . '___';
        eval("abstract class {$className} {abstract public function abstract_method();}");

        $mockClassManager = SoftMocker::createClass($className);

        self::assertTrue($mockClassManager->toClass !== $className);
        self::assertTrue(is_subclass_of($mockClassManager->toClass, $className));

        $mockObjectManager = $mockClassManager->createObjectAndManager();
        $mockObjectManager->getMethodManager('abstract_method')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $mockObjectManager->toObject->abstract_method());
    }

    public function testCreateForFinalClass(): void
    {
        $className = "___test_class_" . uniqid() . '___';
        eval("final class {$className} {}");

        $this->expectException(SoftMockClassCreatorClassIsFinalException::class);
        $this->expectExceptionMessage("Class {$className} is a final class");

        SoftMocker::createClass($className);
    }

    public function testCreateForOtherClassType(): void
    {
        $classTypes = ['trait', 'interface'];

        foreach ($classTypes as $classType)
        {
            $className = "___{$classType}_test_type_class_" . uniqid() . '___';

            eval("{$classType} {$className} {}");

            try {
                SoftMocker::createClass($className);
                $this->fail("Fail for {$classType}");
            }
            catch (SoftMockClassCreatorClassIsNotClassException $error)
            {
                self::assertEquals(
                    "Class {$className} is not a class or abstract class. It is a {$classType}",
                    $error->getMessage()
                );
            }
        }
    }
}
