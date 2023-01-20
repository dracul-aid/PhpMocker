<?php

namespace DraculAid\PhpMocker\tests\Tools\TestTools;

use DraculAid\PhpMocker\Tools\TestTools;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see TestTools::waitThrow()
 *
 * @run php tests/run.php tests/Tools/TestTools/WaitThrowTest.php
 */
class WaitThrowTest extends TestCase
{
    private string $className;

    public function testRun(): void
    {
        $this->createClass();

        self::assertFalse(TestTools::waitThrow([$this->className, 'f1'], [123],  \RuntimeException::class));

        self::assertTrue(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class));
        self::assertTrue(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'test-text'));
        self::assertTrue(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'test-text', null));
        self::assertTrue(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, null, 123));
        self::assertTrue(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'test-text', 123));

        self::assertFalse(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'no-test-text'));
        self::assertFalse(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'no-test-text', null));
        self::assertFalse(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'no-test-text', 123));
        self::assertFalse(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, null, 222));
        self::assertFalse(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'test-text', 222));
        self::assertFalse(TestTools::waitThrow([$this->className, 'f2'], [],  \RuntimeException::class, 'no-test-text', 222));
    }

    public function testWithError(): void
    {
        $this->createClass();
        $this->expectException(\LogicException::class);

        TestTools::waitThrow([$this->className, 'f3'], [],  \RuntimeException::class);
    }

    private function createClass(): void
    {
        $this->className = '___test_class_name_' . uniqid() . '___';

        eval(<<<CODE
            class {$this->className}
            {
                public static function f1(\$arg)
                {
                    return \$arg;
                }
                public static function f2()
                {
                    throw new \RuntimeException('test-text', 123);
                }
                public static function f3()
                {
                    throw new \LogicException();
                }
            }
        CODE);
    }
}
