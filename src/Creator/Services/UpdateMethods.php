<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator\Services;

use DraculAid\PhpMocker\Creator\Services\GeneratorPhpCode;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;

/**
 * Создает для мок-класса мок-методы
 * @see UpdateMethods::exe() Основная функция
 */
class UpdateMethods
{
    /**
     * Хранит схему класса
     */
    private ClassScheme $classScheme;

    /**
     * Менеджер создаваемого мок-класса
     */
    private ClassManager $classManager;

    /**
     * Список имен методов класса, для которых можно получить "мок-метод"
     *
     * @var string[] $mockMethodNames Ключи и значение - строка с именем метода
     */
    private array $mockMethodNames = [];

    /**
     * Создает для мок-класса мок-методы
     *
     * @param   ClassScheme    $classScheme    Схема создаваемого класса
     * @param   ClassManager   $classManager   Менеджер создаваемого мок-класса
     *
     * @return  void
     */
    public static function exe(ClassScheme $classScheme, ClassManager $classManager): void
    {
        $executor = new self();
        $executor->classScheme = $classScheme;
        $executor->classManager = $classManager;

        $executor->runMethods();

        $classManager->setMockMethodNames($executor->mockMethodNames);
    }

    private function __construct() {}

    /**
     * Переопределение методов
     *
     * @return void
     */
    private function runMethods(): void
    {
        foreach ($this->classScheme->methods as $method)
        {
            if ($method->isAbstract)
            {
                continue;
            }

            $this->mockMethodNames[$method->name] = $method->name;

            GeneratorPhpCode::generateMockMethod($method, $this->classManager->index);
        }
    }
}
