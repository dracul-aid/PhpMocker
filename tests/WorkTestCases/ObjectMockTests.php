<?php

namespace DraculAid\PhpMocker\tests\WorkTestCases;

use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodCase;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\MockManager;
use PHPUnit\Framework\TestCase;

/**
 * @run php tests/run.php tests/WorkTestCases/ObjectMockTests.php
 */
class ObjectMockTests extends TestCase
{
    public function testCreateForNew(): void
    {
        $mockClassManager = $this->createMockClassAndReturnManager();

        $testObject = new ($mockClassManager->getToClass())();
        $testManager = MockManager::getForObject($testObject);

        $this->testing($testObject, $testManager);
    }

    public function testCreateForNotNew(): void
    {
        $mockClassManager = $this->createMockClassAndReturnManager();
        $mockClassReflection = new \ReflectionClass($mockClassManager->getToClass());

        $testObject = $mockClassReflection->newInstanceWithoutConstructor();
        $testManager = MockManager::getForObject($testObject);

        $this->testing($testObject, $testManager);
    }

    private function testing(object $testObject, ObjectManager $testManager): void
    {
        self::assertEquals('111', $testObject->f1());

        self::assertNotNull($testManager->getMethodManager('f1'));
        self::assertTrue(is_a($testManager->getMethodManager('f1'), MethodManager::class));
        self::assertTrue(is_a($testManager->getMethodManager('f1')->defaultCase(), MethodCase::class));

        $testManager->getMethodManager('f1')->defaultCase()->setWillReturn('AAA');
        self::assertEquals('AAA', $testObject->f1());
    }

    private function createMockClassAndReturnManager(): ClassManager
    {
        return MockCreator::softClass(
            $this->generateClass()
        );
    }

    private function generateClass(): string
    {
        $testClassName = $this->getNewClassName();

        eval("class {$testClassName} {public function f1() {return '111';}}");

        return $testClassName;
    }

    private function getNewClassName(): string
    {
        return '___test_class_name__' . uniqid();
    }
}
