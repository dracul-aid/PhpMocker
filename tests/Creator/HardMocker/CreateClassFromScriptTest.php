<?php

namespace DraculAid\PhpMocker\tests\Creator\HardMocker;

use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpFileNotFoundException;
use DraculAid\PhpMocker\MockCreator;
use DraculAid\PhpMocker\tests\Creator\HardMocker\_resources\FirstClass;
use DraculAid\PhpMocker\tests\Creator\HardMocker\_resources\OneClass;
use DraculAid\PhpMocker\tests\Creator\HardMocker\_resources\SecondClass;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see HardMocker::createClassFromScript()
 *
 * @run php tests/run.php tests/Creator/HardMocker/CreateClassFromScriptTest.php
 */
class CreateClassFromScriptTest extends TestCase
{
    public function testFileNotFound(): void
    {
        self::expectException(HardMockClassCreatorPhpFileNotFoundException::class);

        HardMocker::createClassFromScript(__DIR__ . '/notcatalog/notfile.php');
    }

    public function testReadClassFromFile(): void
    {
        $classManager = MockCreator::hardFromScript(__DIR__ . '/_resources/OneClass.php');

        self::assertEquals(OneClass::class, $classManager->toClass);
    }

    public function testReadClassFromFileList(): void
    {
        $classManagers = HardMocker::createClassFromScript(__DIR__ . '/_resources/classes.php');

        self::assertCount(2, $classManagers);
        self::assertArrayHasKey(FirstClass::class, $classManagers);
        self::assertArrayHasKey(SecondClass::class, $classManagers);
        self::assertEquals(FirstClass::class, $classManagers[FirstClass::class]->toClass);
        self::assertEquals(SecondClass::class, $classManagers[SecondClass::class]->toClass);
    }
}
