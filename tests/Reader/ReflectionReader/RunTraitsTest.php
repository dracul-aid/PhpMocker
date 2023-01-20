<?php

namespace DraculAid\PhpMocker\tests\Reader\ReflectionReader;

use DraculAid\PhpMocker\Reader\ReflectionReader;
use PHPUnit\Framework\TestCase;

/**
 * Test for @see ReflectionReader::runTraits()
 *
 * @run php tests/run.php tests/Reader/ReflectionReader/RunTraitsTest.php
 */
class RunTraitsTest extends TestCase
{
    public function testClassWithoutTraits(): void
    {
        $classScheme = ReflectionReader::exe(get_class(
            new class {}
        ));

        self::assertIsArray($classScheme->traits);
        self::assertCount(0, $classScheme->traits);
    }

    public function testClassWithTraits(): void
    {
        $this->generateClassWithTraits();

        $classScheme = ReflectionReader::exe('RunTraitsTestClass');

        self::assertIsArray($classScheme->traits);
        self::assertCount(2, $classScheme->traits);

        self::assertEquals(
            [
                '\RunTraitsTestTrait1' => '\RunTraitsTestTrait1',
                '\RunTraitsTestTrait2' => '\RunTraitsTestTrait2',
            ],
            $classScheme->traits
        );
    }

    private function generateClassWithTraits(): void
    {
        eval(
            <<<END
                trait RunTraitsTestTrait1 {}
                trait RunTraitsTestTrait2 {}
                class RunTraitsTestClass {
                    use RunTraitsTestTrait1;
                    use RunTraitsTestTrait2;
                }
            END
        );
    }
}
