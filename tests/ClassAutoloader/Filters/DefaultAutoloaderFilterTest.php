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
        self::assertFalse($testFilter->canBeMock(\DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter::class, ''));
        self::assertFalse($testFilter->canBeMock(\PHPUnit\Framework\TestCase::class, ''));

        $testFilter->namespaceWhiteList->add('DraculAid\PhpMocker\ClassAutoloader\Filters');
        self::assertTrue($testFilter->canBeMock(\DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter::class, ''));
        self::assertFalse($testFilter->canBeMock(\DraculAid\PhpMocker\ClassAutoloader\Autoloader::class, ''));

        $testFilter->classBlackList->add(\DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter::class);
        self::assertFalse($testFilter->canBeMock(\DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter::class, ''));

        $testFilter->classWhiteList->add(\DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter::class);
        self::assertTrue($testFilter->canBeMock(\DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter::class, ''));
    }
}
