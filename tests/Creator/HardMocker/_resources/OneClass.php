<?php

namespace DraculAid\PhpMocker\tests\Creator\HardMocker\_resources;

use DraculAid\PhpMocker\tests\Creator\HardMocker\CreateClassFromScriptTest;

/**
 * Файл (класс), для проверки создания мок-класса для кода из PHP файла
 *
 * @see CreateClassFromScriptTest::testReadClassFromFile() - тест в котором используется файл
 */
class OneClass
{
    public static function f1(): string
    {
        return '111';
    }
}
