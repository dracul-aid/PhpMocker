<?php

namespace DraculAid\PhpMocker\tests\ClassAutoloader\Filters\Storages;

use DraculAid\PhpMocker\ClassAutoloader\Filters\Storages\AutoloaderFilerNamespaceStorage;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see AutoloaderFilerNamespaceStorage
 *
 * @run php tests/run.php tests/ClassAutoloader/Filters/Storages/AutoloaderFilerNamespaceStorageTest.php
 */
class AutoloaderFilerNamespaceStorageTest extends TestCase
{
    private AutoloaderFilerNamespaceStorage $testStorage;

    public function testRun(): void
    {
        $this->testStorage = new AutoloaderFilerNamespaceStorage();

        $this->testStorage->add('stdClass');
        $this->testStorage->add('CatalogName\SubCatalog1');

        $this->testStorage->addList([
            'CatalogName\AndCatalog\SubCatalog2',
            'SubCatalog3',
            'RemoveName1',
            'RemoveName2',
            'RemoveName3',
        ]);

        $this->testStorage->remove('RemoveName2');

        $this->runAssertEqualsList([
            'stdClass',
            'CatalogName\SubCatalog1',
            'CatalogName\AndCatalog\SubCatalog2',
            'SubCatalog3',
            'RemoveName1',
            'RemoveName3',
        ]);

        $this->testStorage->removeList([
            'RemoveName1',
            'RemoveName2',
            'RemoveName3',
        ]);

        $this->runAssertEqualsList([
            'stdClass',
            'CatalogName\SubCatalog1',
            'CatalogName\AndCatalog\SubCatalog2',
            'SubCatalog3',
        ]);

        self::assertTrue($this->testStorage->in('stdClass'));
        self::assertTrue($this->testStorage->in('stdClass\\SubClass'));
        self::assertFalse($this->testStorage->in('NotClass'));

        self::assertTrue($this->testStorage->in('CatalogName\AndCatalog\SubCatalog2\ClassName'));
        self::assertFalse($this->testStorage->in('CatalogName\AndCatalog\SubCatalog333\ClassName'));
    }

    private function runAssertEqualsList(array $classes): void
    {
        $classes = array_combine($classes, $classes);

        self::assertEquals($classes, $this->testStorage->getStorageData());
    }
}
