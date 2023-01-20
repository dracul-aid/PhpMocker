<?php

namespace DraculAid\PhpMocker\tests\ClassAutoloader\Filters\Storages;

use DraculAid\PhpMocker\ClassAutoloader\Filters\Storages\AutoloaderFilerClassNameStorage;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see AutoloaderFilerClassNameStorage
 *
 * @run php tests/run.php tests/ClassAutoloader/Filters/Storages/AutoloaderFilerClassNameStorageTest.php
 */
class AutoloaderFilerClassNameStorageTest extends TestCase
{
    private AutoloaderFilerClassNameStorage $testStorage;

    public function testRun(): void
    {
        $this->testStorage = new AutoloaderFilerClassNameStorage();

        $this->testStorage->add(\stdClass::class);
        $this->testStorage->add(\CatalogName\ClassName1::class);
        $this->testStorage->add(\RemoveName1::class);
        $this->testStorage->add(\RemoveName2::class);
        $this->testStorage->add(\RemoveName3::class);

        $this->testStorage->addList([
            \ClassName2::class,
            \CatalogName\ClassName3::class,
        ]);

        $this->testStorage->remove(\RemoveName2::class);

        $this->runAssertEquals([
            \stdClass::class,
            \CatalogName\ClassName1::class,
            \ClassName2::class,
            \CatalogName\ClassName3::class,
            \RemoveName1::class,
            \RemoveName3::class,
        ]);

        $this->testStorage->removeList([
            \RemoveName1::class,
            \RemoveName2::class,
            \RemoveName3::class,
        ]);

        $this->runAssertEquals([
            \stdClass::class,
            \CatalogName\ClassName1::class,
            \ClassName2::class,
            \CatalogName\ClassName3::class,
        ]);

        self::assertTrue($this->testStorage->in(\stdClass::class));
        self::assertTrue($this->testStorage->in(\CatalogName\ClassName1::class));

        self::assertFalse($this->testStorage->in(\stdClass123123::class));
        self::assertFalse($this->testStorage->in(\CatalogName\AndCatalog\ClassName555::class));
    }

    private function runAssertEquals(array $classes): void
    {
        $classes = array_combine($classes, $classes);

        self::assertEquals($classes, $this->testStorage->getStorageData());
    }
}
