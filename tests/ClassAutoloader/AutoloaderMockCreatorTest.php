<?php

namespace DraculAid\PhpMocker\tests\ClassAutoloader;

use DraculAid\PhpMocker\ClassAutoloader\Autoloader;
use DraculAid\PhpMocker\ClassAutoloader\AutoloaderMockCreator;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathIsNotFileException;
use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\Creator\MockClassInterfaces\HardMockClassInterface;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Tools\TestTools;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see AutoloaderMockCreator
 *
 * @run php tests/run.php tests/ClassAutoloader/AutoloaderMockCreatorTest.php
 */
class AutoloaderMockCreatorTest extends TestCase
{
    public static function getCallCounter(): object
    {
        static $counter;

        if (empty($counter)) $counter = new class() {
            public bool $callCreateMockClass = false;
            public string $saveInFile = '';
            public bool $callLoadClassFromCache = false;

            public function clear(): void
            {
                $this->callCreateMockClass = false;
                $this->saveInFile = '';
                $this->callLoadClassFromCache = false;
            }
        };

        return $counter;
    }

    public function testExeRouting(): void
    {
        $creatorTestClass = $this->createAutoloaderMockCreatorClassForExeRouting();

        // * * *

        /** @see AutoloaderMockCreator::exe() */
        $testResult = $creatorTestClass::exe($this->createAutoloader(false), 'not-class', 'not-file', false);
        self::assertFalse($testResult);
        self::assertFalse(self::getCallCounter()->callCreateMockClass);
        self::assertFalse(self::getCallCounter()->callLoadClassFromCache);

        // * * *

        self::getCallCounter()->clear();
        self::assertTrue(TestTools::waitThrow(
            [$creatorTestClass, 'exe'], /** @see AutoloaderMockCreator::exe() */
            [$this->createAutoloader(false), 'not-class', 'not-file', true],
            PhpMockerAutoloaderPathIsNotFileException::class
        ));
        self::assertFalse(self::getCallCounter()->callCreateMockClass);
        self::assertFalse(self::getCallCounter()->callLoadClassFromCache);

        // * * *

        self::getCallCounter()->clear();
        /** @see AutoloaderMockCreator::exe() */
        $testResult = $creatorTestClass::exe($this->createAutoloader(false), 'class', __FILE__, false);
        self::assertTrue($testResult);
        self::assertTrue(self::getCallCounter()->callCreateMockClass);
        self::assertFalse(self::getCallCounter()->callLoadClassFromCache);

        // * * *

        self::getCallCounter()->clear();
        /** @see AutoloaderMockCreator::exe() */
        $testResult = $creatorTestClass::exe($this->createAutoloader(true), 'class', __FILE__, false);
        self::assertTrue($testResult);
        self::assertFalse(self::getCallCounter()->callCreateMockClass);
        self::assertTrue(self::getCallCounter()->callLoadClassFromCache);
    }

    public function testCreateMockClass(): void
    {
        $testCreator = $this->createAutoloaderMockCreatorClassForCreateMockClass($this->createAutoloader(false));

        $mockClassName = $this->getClassName();
        $testCreator->createMockClass("class {$mockClassName} {}");

        self::assertTrue(class_exists($mockClassName, false));
        self::assertTrue(is_subclass_of($mockClassName, HardMockClassInterface::class));
        self::assertEquals('', self::getCallCounter()->saveInFile);

        // * * *

        $testCreator = $this->createAutoloaderMockCreatorClassForCreateMockClass($this->createAutoloader(true));

        $mockClassName = $this->getClassName();
        $testCreator->createMockClass("class {$mockClassName} {public function f1(){} public function f2(){}}");
        $savePhpCode = self::getCallCounter()->saveInFile;

        self::assertTrue(class_exists($mockClassName, false));
        self::assertTrue(is_subclass_of($mockClassName, HardMockClassInterface::class));
        self::assertTrue($savePhpCode !== '');

        $classManager = ClassManager::getManager($mockClassName);
        $newClassName = $this->getClassName();
        $savePhpCode = str_replace($mockClassName, $newClassName, $savePhpCode);

        $dataForCreateManagers = eval($savePhpCode);

        self::assertTrue(class_exists($newClassName, false));
        self::assertTrue(is_subclass_of($newClassName, HardMockClassInterface::class));

        self::assertEquals([[
            'type' => ClassSchemeType::CLASSES->value,
            'class_name' => $newClassName,
            'driver_name' => HardMocker::class,
            'index' => $classManager->index,
            'mock_method_names' => ['f1' => 'f1', 'f2' => 'f2', ],
        ]], $dataForCreateManagers);
    }

    private function createAutoloaderMockCreatorClassForExeRouting(): string
    {
        return get_class(new class() extends AutoloaderMockCreator {
            public object $callCounter;
            public function __construct()
            {
                $this->callCounter = AutoloaderMockCreatorTest::getCallCounter();
            }
            public function createMockClass(string $originalPhpCode): void
            {
                $this->callCounter->callCreateMockClass = true;
            }
            protected function classWithCache(): bool
            {
                return $this->autoloader->mockClassCachePath !== '';
            }
            protected function loadClassFromCache(): void
            {
                $this->callCounter->callLoadClassFromCache = true;
            }
        });
    }


    private function createAutoloaderMockCreatorClassForCreateMockClass(Autoloader $autoloader): AutoloaderMockCreator
    {
        return new class($autoloader) extends AutoloaderMockCreator {
            public object $callCounter;
            public function __construct(Autoloader $autoloader)
            {
                parent::__construct();
                $this->autoloader = $autoloader;
                $this->callCounter = AutoloaderMockCreatorTest::getCallCounter();
            }
            public function createMockClass(string $originalPhpCode): void
            {
                parent::createMockClass($originalPhpCode);
            }
            protected function saveInFile(string $phpCode): void
            {
                $this->callCounter->saveInFile = $phpCode;
            }
        };
    }

    private function createAutoloader(bool $withCache): Autoloader
    {
        return new class($withCache) extends Autoloader {
            readonly public string $mockClassCachePath;
            public function __construct(bool $withCache)
            {
                $this->mockClassCachePath = $withCache ? 'withCache' : '';
            }
        };
    }

    protected function getClassName(): string
    {
        return '___test_class_name_' . uniqid() . '___';
    }
}
