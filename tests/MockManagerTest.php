<?php

namespace DraculAid\PhpMocker\tests;

use DraculAid\PhpMocker\Exceptions\Managers\ClassManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\Managers\ObjectManagerNotFoundException;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodCase;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\MockManager;
use DraculAid\PhpMocker\NotPublic;
use DraculAid\PhpMocker\Tools\TestTools;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see MockManager
 *
 * @run php tests/run.php tests/MockManagerTest.php
 */
class MockManagerTest extends TestCase
{
    private string $mockClassName;

    /**
     * Test for:
     * @see MockManager::getForClass()
     * @see ClassManager::getManager()
     */
    public function testGetForClass(): void
    {
        $this->createMockClass();

        self::assertTrue(is_object(MockManager::getForClass($this->mockClassName)));
        self::assertTrue(is_a(MockManager::getForClass($this->mockClassName), ClassManager::class));
        self::assertEquals($this->mockClassName, MockManager::getForClass($this->mockClassName)->toClass);

        self::assertNull(ClassManager::getManager(\stdClass::class));
        self::assertNull(ClassManager::getManager(\stdClass::class, false));

        self::assertTrue(
            TestTools::waitThrow(
                [ClassManager::class, 'getManager'], [\stdClass::class, true], ClassManagerNotFoundException::class
            )
        );

        self::assertTrue(
            TestTools::waitThrow(
                [MockManager::class, 'getForClass'], [\stdClass::class],  ClassManagerNotFoundException::class
            )
        );
    }

    /**
     * Test for:
     * @see MockManager::getForObject()
     * @see ObjectManager::getManager()
     */
    public function testGetForObject(): void
    {
        $this->createMockClass();
        $testObject = NotPublic::createObject($this->mockClassName);
        $notMockObject = new \stdClass();

        self::assertTrue(is_object(MockManager::getForObject($testObject)));
        self::assertTrue(is_a(MockManager::getForObject($testObject), ObjectManager::class));
        self::assertTrue($testObject === MockManager::getForObject($testObject)->toObject);

        self::assertNull(ObjectManager::getManager($notMockObject));
        self::assertNull(ObjectManager::getManager($notMockObject, false));

        self::assertTrue(
            TestTools::waitThrow(
                [ObjectManager::class, 'getManager'], [$notMockObject, true], ObjectManagerNotFoundException::class
            )
        );

        self::assertTrue(
            TestTools::waitThrow(
                [MockManager::class, 'getForObject'], [$notMockObject],  ObjectManagerNotFoundException::class
            )
        );
    }

    /**
     * Test for @see MockManager::getForMethod()
     */
    public function testGetForMethod(): void
    {
        $this->createMockClass();
        $testObject = NotPublic::createObject($this->mockClassName);

        self::assertTrue(is_object(MockManager::getForMethod($this->mockClassName, 'getConst')));
        self::assertTrue(is_a(MockManager::getForMethod($this->mockClassName, 'getConst'), MethodManager::class));
        self::assertTrue(is_object(MockManager::getForMethod($testObject, 'getConst')));
        self::assertTrue(is_a(MockManager::getForMethod($testObject, 'getConst'), MethodManager::class));

        self::assertTrue(
            TestTools::waitThrow(
                [MockManager::class, 'getForMethod'], [$this->mockClassName, 'method_not_method'],  MethodManagerNotFoundException::class
            )
        );
        self::assertTrue(
            TestTools::waitThrow(
                [MockManager::class, 'getForMethod'], [$testObject, 'method_not_method'],  MethodManagerNotFoundException::class
            )
        );
    }

    private function createMockClass(): void
    {
        $this->mockClassName = MockCreator::softClass(ClassManager::class)->toClass;
    }
}
