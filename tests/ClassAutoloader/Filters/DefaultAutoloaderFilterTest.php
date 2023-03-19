<?php

namespace DraculAid\PhpMocker\tests\ClassAutoloader\Filters;

use DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see DefaultAutoloaderFilter
 *
 * @run php tests/run.php tests/ClassAutoloader/Filters/DefaultAutoloaderFilterTest.php
 */
class DefaultAutoloaderFilterTest extends TestCase
{
    public function testRun(): void
    {
        $testFilter = new DefaultAutoloaderFilter();

        self::assertTrue($testFilter->canBeMock(\stdClass::class, ''));
        self::assertTrue($testFilter->canBeMock(\CatalogName\ClassName1::class, ''));
        self::assertFalse($testFilter->canBeMock(\PHPUnit\Framework\TestCase::class, ''));

        $testFilter->namespaceBlackList->add('BlackListNamespace');
        self::assertFalse($testFilter->canBeMock(\BlackListNamespace\TestClass::class, ''));

        $testFilter->namespaceWhiteList->add('BlackListNamespace\WhiteNamespace');
        self::assertTrue($testFilter->canBeMock(\BlackListNamespace\WhiteNamespace\TrueClass::class, ''));
        self::assertFalse($testFilter->canBeMock(\BlackListNamespace\FalseClass::class, ''));

        $testFilter->classBlackList->add(\BlackListNamespace\WhiteNamespace\TrueClass::class);
        self::assertFalse($testFilter->canBeMock(\BlackListNamespace\WhiteNamespace\TrueClass::class, ''));

        $testFilter->classWhiteList->add(\BlackListNamespace\FalseClass::class);
        self::assertTrue($testFilter->canBeMock(\BlackListNamespace\FalseClass::class, ''));
    }
}
