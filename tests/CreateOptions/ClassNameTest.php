<?php

namespace DraculAid\PhpMocker\tests\CreateOptions;

use DraculAid\PhpMocker\CreateOptions\ClassName;
use DraculAid\PhpMocker\Creator\MockerOptions\SoftMockerOptions;

/**
 * Test for @see ClassName
 *
 * @run php tests/run.php tests/CreateOptions/ClassNameTest.php
 */
class ClassNameTest extends AbstractCreateOptions
{
    public function testSetNewName(): void
    {
        $this->runTesting(
            $this->getNewClassName(),
            $this->getNewClassName()
        );
        $this->runTesting(
            'test_namespace\\' . $this->getNewClassName(),
            $this->getNewClassName()
        );
        $this->runTesting(
            $this->getNewClassName(),
            'test_namespace\\' . $this->getNewClassName()
        );
    }

    public function testSetAutoName(): void
    {
        $className = $this->getNewClassName();
        $classScheme = $this->getClassScheme($className);

        $optionsObject = new ClassName(true);
        $optionsObject($classScheme, new SoftMockerOptions());

        self::assertTrue($className !== $classScheme->getFullName());
    }

    private function runTesting(string $className, string $classNewName): void
    {
        $classScheme = $this->getClassScheme($className);

        $optionsObject = new ClassName($classNewName);
        $optionsObject($classScheme, new SoftMockerOptions());

        self::assertEquals($classNewName, $classScheme->getFullName());
    }
}
