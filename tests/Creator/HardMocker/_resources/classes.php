<?php

/**
 * Файл, для проверки создания мок-классов для кода из PHP файла
 *
 * @see \DraculAid\PhpMocker\tests\Creator\HardMocker\CreateClassFromScriptTest::testReadClassFromFileList() - тест в котором используется файл
 */

namespace DraculAid\PhpMocker\tests\Creator\HardMocker\_resources;

abstract class FirstClass {}

final class SecondClass extends FirstClass {}
