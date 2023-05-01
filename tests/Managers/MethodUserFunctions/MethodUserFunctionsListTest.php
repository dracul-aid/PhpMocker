<?php

namespace DraculAid\PhpMocker\tests\Managers\MethodUserFunctions;

use DraculAid\PhpMocker\Managers\MethodUserFunctions\MethodUserFunctionsList;
use DraculAid\PhpMocker\Managers\Tools\CallResult;

/**
 * Test for @see \DraculAid\PhpMocker\Managers\MethodUserFunctions\MethodUserFunctionsList
 *
 * @run php tests/run.php tests/Managers/MethodUserFunctions/OverwritePropertyMethodUserFunctionTest.php
 */
class MethodUserFunctionsListTest extends AbstractMethodUserFunctionTesting
{
    public function testRun(): void
    {
        $this->createObjects(false);
        $testFunction = new MethodUserFunctionsList([], false);

        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertNull($callResult);

        // * * *

        $this->createObjects(false);
        $testFunction = new MethodUserFunctionsList([], true, false);

        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertFalse($callResult->isCanReturn);

        // * * *

        $this->createObjects(false);
        $testFunction = new MethodUserFunctionsList([], true, true);

        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertTrue($callResult->isCanReturn);
        self::assertNull($callResult->canReturnData);

        // * * *

        $this->createObjects(false);
        $testFunction = new MethodUserFunctionsList([], true, true, 'return_string');

        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertTrue($callResult->isCanReturn);
        self::assertEquals('return_string', $callResult->canReturnData);

        // * * *

        $checkCallF1 = false;
        $checkCallF2 = true;
        $this->createObjects(false);
        $testFunction = new MethodUserFunctionsList([
            static function ($hasCalled, $methodManager, MethodUserFunctionsList $functionsList) use (&$checkCallF1) {$checkCallF1 = true; $functionsList->resultCallResult->isCanReturn = true;},
            null,
            static function ($hasCalled, $methodManager, MethodUserFunctionsList $functionsList) use (&$checkCallF2) {$checkCallF2 = true; $functionsList->resultCallResult->canReturnData = 'return_string';}
        ], true,  false);

        $callResult = $testFunction($this->hasCalled, $this->methodManager);
        self::assertTrue(is_a($callResult, CallResult::class));
        self::assertTrue($callResult->isCanReturn);
        self::assertEquals('return_string', $callResult->canReturnData);
        self::assertTrue($checkCallF1);
        self::assertTrue($checkCallF2);
    }
}
