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
            public string $arg1 = '';
            public function __construct(string $arg1 = '', $arg2 = '')
            {
                $this->arg1 = $arg1;
            }
        };

        $methods = ClassTools::getMethodArgumentNames($object, '__construct');
        self::assertEquals(['arg1' => 'arg1', 'arg2' => 'arg2'], $methods);
    }

    /**
     * Test for
     * @see ClassTools::getNamespace()
     * @see ClassTools::getNameWithoutNamespace()
     * @see ClassTools::getNameAndNamespace()
     */
    public function testGetNameOrNamespace(): void
    {
        self::assertEquals('catalog\\subcatalog', ClassTools::getNamespace('catalog\\subcatalog\\class'));
        self::assertEquals('catalog', ClassTools::getNamespace('catalog\\class'));
        self::assertEquals('', ClassTools::getNamespace('class'));

        // * * *

        self::assertEquals('class', ClassTools::getNameWithoutNamespace('catalog\\subcatalog\\class'));
        self::assertEquals('class', ClassTools::getNameWithoutNamespace('catalog\\class'));
        self::assertEquals('class', ClassTools::getNameWithoutNamespace('class'));

        // * * *

        ClassTools::getNameAndNamespace('catalog\\subcatalog\\class', $namespace, $name);
        self::assertEquals('catalog\\subcatalog', $namespace);
        self::assertEquals('class', $name);

        ClassTools::getNameAndNamespace('catalog\\class', $namespace, $name);
        self::assertEquals('catalog', $namespace);
        self::assertEquals('class', $name);

        ClassTools::getNameAndNamespace('class', $namespace, $name);
        self::assertEquals('', $namespace);
        self::assertEquals('class', $name);
    }
}
