<?php

namespace DraculAid\PhpMocker\tests\MethodMockCall;

use DraculAid\PhpMocker\Creator\SoftMocker;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Tools\CallableObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for:
 * @see \DraculAid\PhpMocker\Managers\MethodCase
 * @see \DraculAid\PhpMocker\Managers\Tools\CallResult
 *
 * @run php tests/run.php tests/MethodMockCall/CallCaseTest.php
 */
class CallCaseTest extends TestCase
{
    public function testCallWithoutMock(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );

        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager(false, [], $testObject);
        $methodManager = $objectManager->getMethodManager('test');

        self::assertEquals(0, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);

        self::assertEquals('testABC', $testObject->test('ABC'));
        self::assertEquals(1, $methodManager->countCall);
        self::assertEquals(1, $methodManager->countCallWithoutCases);
        self::assertEquals(0, $methodManager->defaultCase()->countCall);
        self::assertEquals(0, $methodManager->defaultCase()->countAllCall);
    }

    public function testCallWithDefaultCase(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager(false, [], $testObject);
        $methodManager = $objectManager->getMethodManager('test');

        $methodManager->defaultCase()->setWillReturn('callWithDefaultCase111');
        self::assertEquals('callWithDefaultCase111', $testObject->test('ABC'));
        self::assertEquals('callWithDefaultCase111', $testObject->test('111'));
        self::assertEquals(2, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(2, $methodManager->defaultCase()->countCall);
        self::assertEquals(2, $methodManager->defaultCase()->countAllCall);

        $methodManager->defaultCase()->setWillReturn('callWithDefaultCase222', true);
        self::assertEquals(2, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(0, $methodManager->defaultCase()->countCall);
        self::assertEquals(2, $methodManager->defaultCase()->countAllCall);
        self::assertEquals('callWithDefaultCase222', $testObject->test('ABC'));
        self::assertEquals(3, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(1, $methodManager->defaultCase()->countCall);
        self::assertEquals(3, $methodManager->defaultCase()->countAllCall);

        $methodManager->defaultCase()->setWillReturn('callWithDefaultCase333');
        self::assertEquals(3, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(1, $methodManager->defaultCase()->countCall);
        self::assertEquals(3, $methodManager->defaultCase()->countAllCall);
        self::assertEquals('callWithDefaultCase333', $testObject->test('ABC'));
        self::assertEquals(4, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(2, $methodManager->defaultCase()->countCall);
        self::assertEquals(4, $methodManager->defaultCase()->countAllCall);

        $methodManager->defaultCase()->setWillReturnClear();
        self::assertEquals(4, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(2, $methodManager->defaultCase()->countCall);
        self::assertEquals(4, $methodManager->defaultCase()->countAllCall);
        self::assertEquals('testABC', $testObject->test('ABC'));
        self::assertEquals(5, $methodManager->countCall);
        self::assertEquals(1, $methodManager->countCallWithoutCases);
        self::assertEquals(2, $methodManager->defaultCase()->countCall);
        self::assertEquals(4, $methodManager->defaultCase()->countAllCall);
    }

    public function testCallWithArgumentsCase(): void
    {
        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager(false, [], $testObject);
        $methodManager = $objectManager->getMethodManager('test');
        $methodCaseAAA = $methodManager->case('AAA');

        self::assertEquals('testABC', $testObject->test('ABC'));
        self::assertEquals(1, $methodManager->countCall);
        self::assertEquals(1, $methodManager->countCallWithoutCases);
        self::assertEquals(0, $methodManager->defaultCase()->countCall);
        self::assertEquals(0, $methodManager->defaultCase()->countAllCall);
        self::assertEquals(0, $methodCaseAAA->countCall);
        self::assertEquals(0, $methodCaseAAA->countAllCall);

        $methodCaseAAA->setWillReturn('111');
        self::assertEquals('111', $testObject->test('AAA'));
        self::assertEquals('testABC', $testObject->test('ABC'));
        self::assertEquals(3, $methodManager->countCall);
        self::assertEquals(2, $methodManager->countCallWithoutCases);
        self::assertEquals(0, $methodManager->defaultCase()->countCall);
        self::assertEquals(0, $methodManager->defaultCase()->countAllCall);
        self::assertEquals(1, $methodCaseAAA->countCall);
        self::assertEquals(1, $methodCaseAAA->countAllCall);

        $methodManager->defaultCase()->setWillReturn('callWithDefaultCase');
        self::assertEquals('111', $testObject->test('AAA'));
        self::assertEquals('callWithDefaultCase', $testObject->test('ABC'));
        self::assertEquals(5, $methodManager->countCall);
        self::assertEquals(2, $methodManager->countCallWithoutCases);
        self::assertEquals(1, $methodManager->defaultCase()->countCall);
        self::assertEquals(1, $methodManager->defaultCase()->countAllCall);
        self::assertEquals(2, $methodCaseAAA->countCall);
        self::assertEquals(2, $methodCaseAAA->countAllCall);
    }

    public function testWithUserFunctionNotReturn(): void
    {
        $functionOn = null;

        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager(false, [], $testObject);
        $methodManager = $objectManager->getMethodManager('test');

        $methodManager->userFunction = new CallableObject(function (HasCalled $calledData, MethodManager $manager) use (&$functionOn) {
            static $_i = 0;
            $functionOn = $manager->name . "-{$calledData->arguments[0]}-" . ++$_i;
        });
        self::assertEquals('testABC', $testObject->test('ABC'));
        self::assertEquals(1, $methodManager->countCall);
        self::assertEquals(1, $methodManager->countCallWithoutCases);
        self::assertEquals(0, $methodManager->countCallUserFunctionReturn);
        self::assertEquals(0, $methodManager->defaultCase()->countCall);
        self::assertEquals(0, $methodManager->defaultCase()->countAllCall);
        self::assertEquals('test-ABC-1', $functionOn);

        $methodManager->defaultCase()->setWillReturn('callWithDefaultCase');
        self::assertEquals('callWithDefaultCase', $testObject->test('ABC'));
        self::assertEquals(2, $methodManager->countCall);
        self::assertEquals(1, $methodManager->countCallWithoutCases);
        self::assertEquals(0, $methodManager->countCallUserFunctionReturn);
        self::assertEquals(1, $methodManager->defaultCase()->countCall);
        self::assertEquals(1, $methodManager->defaultCase()->countAllCall);
        self::assertEquals('test-ABC-2', $functionOn);
    }

    public function testWithUserFunctionAndReturn(): void
    {
        $functionOn = null;

        $scheme = ReflectionReader::exe(
            $this->generateClass()
        );
        $classManager = SoftMocker::createClass($scheme->getFullName());
        $objectManager = $classManager->createObjectAndManager(false, [], $testObject);
        $methodManager = $objectManager->getMethodManager('test');

        $methodManager->userFunction = new CallableObject(function (HasCalled $calledData, MethodManager $manager) use (&$functionOn) {
            static $_i = 0;
            $functionOn = $manager->name . "-{$calledData->arguments[0]}-" . ++$_i;
            return new CallResult(true, "QWERTY-{$_i}");
        });

        self::assertEquals('QWERTY-1', $testObject->test('ABC'));
        self::assertEquals(1, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(1, $methodManager->countCallUserFunctionReturn);
        self::assertEquals(0, $methodManager->defaultCase()->countCall);
        self::assertEquals(0, $methodManager->defaultCase()->countAllCall);
        self::assertEquals('test-ABC-1', $functionOn);

        $methodManager->defaultCase()->setWillReturn('callWithDefaultCase');
        self::assertEquals('QWERTY-2', $testObject->test('ABC'));
        self::assertEquals(2, $methodManager->countCall);
        self::assertEquals(0, $methodManager->countCallWithoutCases);
        self::assertEquals(2, $methodManager->countCallUserFunctionReturn);
        self::assertEquals(0, $methodManager->defaultCase()->countCall);
        self::assertEquals(0, $methodManager->defaultCase()->countAllCall);
        self::assertEquals('test-ABC-2', $functionOn);
    }

    private function generateClass(): string
    {
        $className = 'generateClassForCloseElements' . uniqid();

        eval(
        <<<END
                class {$className}
                {
                    public function test(string \$t): string
                    {
                        return 'test' . \$t;
                    }
                }
            END
        );

        return $className;
    }
}
