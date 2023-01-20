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

use DraculAid\PhpMocker\Creator\AbstractMocker;
use DraculAid\PhpMocker\Creator\Services\GeneratorPhpCode\GeneratorCallMockManagerPhpCode;
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Schemes\MethodArgumentScheme;

/**
 * Генераторы PHP кода для методов мок-классов
 *
 * Оглавление:
 * @see GeneratorPhpCode::generateCallParent() - Создаст PHP код с вызовом методом своего родителя
 * @see GeneratorPhpCode::generateMockMethod() - Создаст PHP код для мок-метода
 */
class GeneratorPhpCode
{
    /**
     * Создаст PHP код с вызовом методом своего родителя
     *
     * @param   MethodScheme   $methodScheme   Схема метода, для которого создается код
     *
     * @return  string
     */
    public static function generateCallParent(MethodScheme $methodScheme): string
    {
        if ($methodScheme->getClassScheme()->parent === '')
        {
            return '';
        }

        // * * *

        $_return = '';

        if ($methodScheme->canReturnValues())
        {
            $_return .= 'return ';
        }

        $_return .= "parent::{$methodScheme->name}(" . self::argumentsToStringVarName($methodScheme->arguments) . ');';


        // * * *

        return "if ( method_exists( parent::class, '{$methodScheme->name}' ) ) {{$_return}}";
    }

    /**
     * Создаст PHP код для мок-метода
     *
     * @param   MethodScheme   $methodScheme   Схема метода, для которого создается код
     * @param   string         $index          Уникальный идентификатор мок-класса
     *
     * @return  void
     */
    public static function generateMockMethod(MethodScheme $methodScheme, string $index): void
    {
        $phpCode = GeneratorCallMockManagerPhpCode::exe($methodScheme, $index);

        if ($methodScheme->innerPhpCode) $phpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . "{$methodScheme->innerPhpCode}";
        elseif (!$methodScheme->isDefine) $phpCode .= AbstractMocker::NEW_LINE_FOR_METHOD_CODE . self::generateCallParent($methodScheme);

        // * * *

        $methodScheme->innerPhpCode = $phpCode;
        $methodScheme->isDefine = true;
    }

    /**
     * Создаст PHP код для конструктора мок-класса
     *
     * @param   MethodScheme   $methodScheme   Схема метода, для которого создается код
     * @param   string         $index          Уникальный идентификатор мок-класса
     *
     * @return  void
     */
    public static function generateMockConstructor(MethodScheme $methodScheme, string $index): void
    {
        GeneratorPhpCode::generateMockMethod($methodScheme, $index);

        /** @see ObjectManager::__construct()  */
        $methodScheme->innerPhpCode = AbstractMocker::NEW_LINE_FOR_METHOD_CODE
            . 'new \\' . ObjectManager::class . '($this);'
            . AbstractMocker::NEW_LINE_FOR_METHOD_CODE
            . $methodScheme->innerPhpCode;
    }

    /**
     * Преобразует список аргументов в строку с переменными (именами аргументов)
     *
     * @param   MethodArgumentScheme[]   $arguments   Массив со схемами-аргументов функций
     *
     * @return  string
     */
    private static function argumentsToStringVarName(array $arguments): string
    {
        $_return = [];

        foreach ($arguments as $name => $schemes)
        {
            $_return[] = "\${$name}";
        }

        return implode(',', $_return);
    }
}
