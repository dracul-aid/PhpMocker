<?php

namespace DraculAid\PhpMocker\tests\CodeGenerator\ClassBasicGenerator;

use DraculAid\PhpMocker\CodeGenerator\ClassGenerator;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ClassBasicGenerator::exeForTraits()
 *
 * @run php tests/run.php tests/CodeGenerator/ClassBasicGenerator/ExeForTraitsTest.php
 */
class ExeForTraitsTest extends TestCase
{
    private ClassScheme $classScheme;
    private ClassScheme $newClassScheme;

    public function testWithoutTraits(): void
    {
        $this->classScheme = ReflectionReader::exe(get_class(
            new class {}
        ));

        $this->createCloneClass();

        self::assertCount(0, $this->newClassScheme->traits);
    }

    public function testWithTraits(): void
    {
        $this->generateTraits();

        $this->classScheme = ReflectionReader::exe(get_class(
            new class {
                use \ExeForTraitsTestTrait1;
                use \ExeForTraitsTestTrait2;
            }
        ));

        $this->createCloneClass();

        self::assertCount(2, $this->newClassScheme->traits);
    }

    private function createCloneClass(): void
    {
        $className = 'ExeForTraitsTestNewClassName' . uniqid();

        $this->classScheme->setFullName($className);
        $this->classScheme->isAnonymous = false;

        ClassGenerator::generateCodeAndEval($this->classScheme);

        $this->newClassScheme = ReflectionReader::exe($className);
    }

    private function generateTraits(): void
    {
        eval(
            <<<'END'
                trait ExeForTraitsTestTrait1 {}
                trait ExeForTraitsTestTrait2 {}
            END
        );
    }
}
