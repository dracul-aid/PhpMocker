<?php

namespace DraculAid\PhpMocker\tests\WorkTestCases;

use DraculAid\PhpMocker\Exceptions\Managers\MethodIsNotConstructorException;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodCase;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\Tools\CallableObject;
use DraculAid\PhpMocker\Tools\TestTools;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see MethodCase
 *
 * @run php tests/run.php tests/WorkTestCases/CallCaseTest.php
 */
class CallCaseTest extends TestCase
{
    private string $className;
    private ClassManager $classManager;

    public function testRun(): void
    {
        $this->createClassAndManager();
        $methodManager = $this->classManager->getMethodManager('f');

        // * * *

        self::assertEquals('f_a', $this->className::f('a'));

        // * * *

        $methodManager->defaultCase()->setWillReturn('DEFAULT');
        $methodManager->case('a')->setWillReturn('AAA');
        self::assertEquals('AAA', $this->className::f('a'));
        self::assertEquals('DEFAULT', $this->className::f('b'));

        // * * *

        $methodManager->case('a')->setUserFunction(new CallableObject(static function(){ return 'XXX'; }));
        self::assertEquals('AAA', $this->className::f('a'));
        self::assertEquals('DEFAULT', $this->className::f('b'));

        // * * *

        $methodManager->case('a')->setUserFunction(new CallableObject(static function(){ return new CallResult(true, 'aaa'); }));
        self::assertEquals('aaa', $this->className::f('a'));
        self::assertEquals('DEFAULT', $this->className::f('b'));

        // * * *

        $methodManager->case('a')->setWillException(new \RuntimeException('test-text'));
        self::assertEquals('DEFAULT', $this->className::f('b'));

        try {
            self::assertEquals('aaa', $this->className::f('a'));
            $this->fail();
        }
        catch (\RuntimeException $error)
        {
            self::assertEquals('test-text', $error->getMessage());
        }

        // * * *

        self::assertEquals(9, $methodManager->countCall);
        self::assertEquals(1, $methodManager->countCallWithoutCases);
        self::assertEquals(4, $methodManager->defaultCase()->countCall);
        self::assertEquals(4, $methodManager->case('a')->countCall);

        // * * *

        $objectManager = $this->classManager->createObjectAndManager();
        $methodManager = $objectManager->getMethodManager('__construct');
        $methodManager->defaultCase()->setClearConstructor();
        self::assertNull($methodManager->call());

        $objectManager = $this->classManager->createObjectAndManager();
        $methodManager = $objectManager->getMethodManager('__construct');
        $methodManager->defaultCase()->setWillReturn('string')->setClearConstructor([], 'new_string');
        self::assertEquals('new_string', $methodManager->call());

        $objectManager = $this->classManager->createObjectAndManager();
        $methodManager = $objectManager->getMethodManager('f2');
        self::assertTrue(
            TestTools::waitThrow([$methodManager->defaultCase(), 'setClearConstructor'], [], MethodIsNotConstructorException::class)
        );
    }

    private function createClassAndManager(): void
    {
        $this->className = '___test_class_name_' . uniqid() . '___';

        $this->classManager = MockCreator::hardFromPhpCode(<<<CODE
                class {$this->className} 
                {
                    public static function f(mixed \$arg): string
                    {
                        return 'f_' . \$arg;                    
                    }
                    public function __construct() {return 'not_set_string';}
                    public function f2() {}
                }
            CODE
        );
    }
}
