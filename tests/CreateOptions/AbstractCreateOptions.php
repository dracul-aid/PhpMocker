<?php

namespace DraculAid\PhpMocker\tests\CreateOptions;

use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use PHPUnit\Framework\TestCase;

/**
 * Абстрактный класс для тестирования "параметров создания мок-классов"
 */
abstract class AbstractCreateOptions extends TestCase
{
    protected function getNewClassName(): string
    {
        return '___test_class_name_' . uniqid() . '___';
    }

    protected function getClassScheme(string $name): ClassScheme
    {
        return new ClassScheme(ClassSchemeType::CLASSES, $name);
    }
}
