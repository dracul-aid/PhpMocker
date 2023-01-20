<?php

namespace DraculAid\PhpMocker\tests\Tools;

use DraculAid\PhpMocker\Tools\ClassTools;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ClassTools
 *
 * @run php tests/run.php tests/Tools/ClassToolsTest.php
 */
class ClassToolsTest extends TestCase
{
    /**
     * Test for @see ClassTools::getMethodArgumentNames
     */
    public function testGetMethodArgumentNames(): void
    {
        $methods = ClassTools::getMethodArgumentNames(ClassTools::class, 'getMethodArgumentNames');
        self::assertEquals(['classOrObject' => 'classOrObject', 'methodName' => 'methodName'], $methods);

        // * * *

        $object = new class() {
            public function __construct(public string $arg1 = '', $arg2 = '') {}
        };

        $methods = ClassTools::getMethodArgumentNames($object, '__construct');
        self::assertEquals(['arg1' => 'arg1', 'arg2' => 'arg2'], $methods);
    }
}
