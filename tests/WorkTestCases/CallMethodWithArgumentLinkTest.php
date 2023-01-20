<?php

namespace DraculAid\PhpMocker\tests\WorkTestCases;

use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\Tools\CallableObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see MethodCase
 *
 * @run php tests/run.php tests/WorkTestCases/CallMethodWithArgumentLinkTest.php
 */
class CallMethodWithArgumentLinkTest extends TestCase
{
    private string $className;
    private MethodManager $methodManager;

    public function testMethodManagerUserFunction(): void
    {
        $this->createClass();

        $arg2 = '';
        self::assertEquals('F:1', $this->methodManager->call('1', $arg2));

        // * * *

        $this->methodManager->setUserFunction(static function (HasCalled $calledData, MethodManager $manager) {
            $calledData->arguments['arg1'] = 'ABC';
        });
        self::assertEquals('F:ABC', $this->methodManager->call('1', $arg2));

        $this->methodManager->setUserFunction(null);
        self::assertEquals('F:1', $this->methodManager->call('1', $arg2));

        // * * *

        $this->className::f('1', $arg2);
        self::assertEquals('F-call-', $arg2);

        $this->methodManager->setUserFunction(static function (HasCalled $calledData, MethodManager $manager) {
            $calledData->arguments['arg2'] = 'XYZ';
        });

        $this->className::f('1', $arg2);
        self::assertEquals('F-call-XYZ', $arg2);
    }

    public function testSetArgumentsFromMethodCase(): void
    {
        $this->createClass();

        $arg2 = '';
        $this->methodManager->defaultCase()->setRewriteArguments(['arg2' => 'ABC']);

        // * * *

        self::assertEquals('F:1', $this->className::f('1', $arg2));
        self::assertEquals('F-call-ABC', $arg2);

        // * * *

        $this->methodManager->defaultCase()->setWillReturn('112233');
        self::assertEquals('112233', $this->className::f('1', $arg2));
        self::assertEquals('ABC', $arg2);
    }

    private function createClass(): void
    {
        $this->className = '___test_class_name_' . uniqid() . '___';

        $this->methodManager = MockCreator::hardFromPhpCode(<<<CODE
            class {$this->className} {
                public static function f(string \$arg1, string &\$arg2): string
                {
                    \$arg2 = 'F-call-' . \$arg2;
                    return 'F:' . \$arg1;
                }
            }
        CODE)->getMethodManager('f');
    }
}
