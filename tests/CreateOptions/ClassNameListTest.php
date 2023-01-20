<?php

namespace DraculAid\PhpMocker\tests\CreateOptions;

use DraculAid\PhpMocker\CreateOptions\ClassNameList;
use DraculAid\PhpMocker\Creator\MockerOptions\SoftMockerOptions;

/**
 * Test for @see ClassNameList
 *
 * @run php tests/run.php tests/CreateOptions/ClassNameListTest.php
 */
class ClassNameListTest extends AbstractCreateOptions
{
    public function testRun(): void
    {
        $className1 = $this->getNewClassName();
        $className2 = $this->getNewClassName();
        $className3 = $this->getNewClassName();
        $newClassName3 = $this->getNewClassName();

        $classSchemes = [
            $this->getClassScheme($className1),
            $this->getClassScheme($className2),
            $this->getClassScheme($className3),
        ];
        $optionsObject = new ClassNameList([
            $className2 => true,
            $className3 => $newClassName3,
        ]);

        foreach ($classSchemes as $scheme)
        {
            $optionsObject($scheme, new SoftMockerOptions());
        }

        self::assertEquals($className1, $classSchemes[0]->getFullName());
        self::assertTrue($className2 !== $classSchemes[1]->getFullName());
        self::assertEquals($newClassName3, $classSchemes[2]->getFullName());
    }
}
