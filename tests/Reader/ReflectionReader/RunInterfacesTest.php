<?php

namespace DraculAid\PhpMocker\tests\Reader\ReflectionReader;

use DraculAid\PhpMocker\Reader\ReflectionReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ReflectionReader::runInterfaces()
 *
 * @run php tests/run.php tests/Reader/ReflectionReader/RunInterfacesTest.php
 */
class RunInterfacesTest extends TestCase
{
    public function testWithoutInterfaces(): void
    {
        $classScheme = ReflectionReader::exe(
            $this->createClassForWithoutInterfaces()
        );

        self::assertIsArray($classScheme->interfaces);
        self::assertCount(0, $classScheme->interfaces);
    }

    public function testInterfacesInClassAndNotIntParent(): void
    {
        $classScheme = ReflectionReader::exe(
            $this->createClassForInterfacesInClassAndNotIntParent()
        );

        self::assertIsArray($classScheme->interfaces);
        self::assertCount(2, $classScheme->interfaces);

        self::assertEquals(
            [
                '\Stringable' => '\Stringable',
                '\SplObserver' => '\SplObserver',
            ],
            $classScheme->interfaces
        );
    }

    public function testInterfacesNotInClassAndIntParent(): void
    {
        $classScheme = ReflectionReader::exe(
            $this->createClassForInterfacesNotInClassAndIntParent()
        );

        self::assertIsArray($classScheme->interfaces);
        self::assertCount(0, $classScheme->interfaces);
    }

    public function testInterfacesInClassAndIntParent(): void
    {
        $classScheme = ReflectionReader::exe(
            $this->createClassForInterfacesInClassAndIntParent()
        );

        self::assertIsArray($classScheme->interfaces);
        self::assertCount(1, $classScheme->interfaces);

        self::assertEquals(
            [
                '\SplObserver' => '\SplObserver',
            ],
            $classScheme->interfaces
        );
    }

    private function createClassForWithoutInterfaces(): string
    {
        return get_class(
            new class {}
        );
    }

    private function createClassForInterfacesInClassAndNotIntParent(): string
    {
        return get_class(
            new class extends \stdClass implements \Stringable, \SplObserver {
                public function __toString(): string
                {
                    return "";
                }
                public function update(\SplSubject $subject): void
                {
                }
            }
        );
    }

    private function createClassForInterfacesNotInClassAndIntParent(): string
    {
        return get_class(
            new class extends \Exception {
            }
        );
    }

    private function createClassForInterfacesInClassAndIntParent(): string
    {
        return get_class(
            new class extends \Exception implements \Stringable, \SplObserver {
                public function update(\SplSubject $subject): void
                {
                }
            }
        );
    }
}
