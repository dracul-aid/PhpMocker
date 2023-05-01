<?php

namespace DraculAid\PhpMocker\tests\Managers\MethodUserFunctions;

use DraculAid\PhpMocker\Managers\MethodUserFunctions\OverwritePropertyMethodUserFunction;
use DraculAid\PhpMocker\Managers\Tools\CallResult;

/**
 * Test for @see \DraculAid\PhpMocker\Managers\MethodUserFunctions\OverwritePropertyMethodUserFunction
 *
 * @run php tests/run.php tests/Managers/MethodUserFunctions/OverwritePropertyMethodUserFunctionTest.php
 */
class OverwritePropertyMethodUserFunctionTest extends AbstractMethodUserFunctionTesting
{
    public function testRun(): void
    {
        $this->createObjects(false);
        $testFunction = new OverwritePropertyMethodUserFunction([], false, false);
        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertNull($callResult);

        // * * *

        $this->createObjects(false);
        $testFunction = new OverwritePropertyMethodUserFunction([], true, false);
        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertFalse($callResult->isCanReturn);
        self::assertEquals('not_value', $this->classManager->toClass::$staticVar);

        // * * *

        $this->createObjects(false);
        $testFunction = new OverwritePropertyMethodUserFunction(['staticVar' => 123], true, true);
        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertTrue($callResult->isCanReturn);
        self::assertNull($callResult->canReturnData);
        self::assertEquals(123, $this->classManager->toClass::$staticVar);

        // * * *

        $this->createObjects(true);
        $testFunction = new OverwritePropertyMethodUserFunction([], true, true, 'return_value');
        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertTrue($callResult->isCanReturn);
        self::assertEquals('return_value', $callResult->canReturnData);
        self::assertEquals('not_value', $this->objectManager->toObject->var);
        self::assertEquals('not_value', $this->classManager->toClass::$staticVar);

        // * * *

        $this->createObjects(true);
        $testFunction = new OverwritePropertyMethodUserFunction(['var' => 123], true, false);
        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertFalse($callResult->isCanReturn);
        self::assertEquals(123, $this->objectManager->toObject->var);
        self::assertEquals('not_value', $this->classManager->toClass::$staticVar);
    }
}
