<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\CodeGenerator;

use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ConstantScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;

/**
 * Создает PHP строку с константами класса из схем констант @see ConstantScheme
 *
 * Это класс, для разгрузки кода @see ClassGenerator - Генератора PHP кода класса по схеме класса
 *
 * Оглавление:
 * @see ConstantsGenerator::exe() - Генератор PHP кода констант
 */
class ConstantsGenerator
{
    /**
     * Создает PHP строку с константами класса
     *
     * @param   ClassScheme  $classScheme   Схема класса
     *
     * @return  string
     */
    public static function exe(ClassScheme $classScheme): string
    {
        if ($classScheme->type === ClassSchemeType::TRAITS || count($classScheme->constants) === 0)
        {
            return '';
        }

        // * * *
        
        $_return = '';
        
        foreach ($classScheme->constants as $constant)
        {
            // если эта константа была определена не в этом классе - пропустим ее генерацию
            if (!$constant->isDefine)
            {
                continue;
            }

            $_return .= AttributesGenerator::exe($constant);

            if ($constant->isEnumCase) $_return .= self::enumCaseCode($constant);
            else $_return .= self::constCode($constant);
        }

        return $_return;
    }

    /**
     * Создаст строку PHP кода с определением варианта перечисления
     *
     * @param   ConstantScheme   $constantScheme   Схема константы
     *
     * @return  string
     */
    private static function enumCaseCode(ConstantScheme $constantScheme): string
    {
        $value = $constantScheme->getValuePhpCode();

        $_return = ClassGenerator::NEW_LINE_FOR_ELEMENTS;
        $_return .= "case {$constantScheme->name}";
        if ($value) $_return .= " = {$value}";
        $_return .= ";\n";

        return $_return;
    }

    /**
     * Создаст строку PHP кода с определением константы класса
     *
     * @param   ConstantScheme   $constantScheme   Схема константы
     *
     * @return  string
     */
    private static function constCode(ConstantScheme $constantScheme): string
    {
        $_return = ClassGenerator::NEW_LINE_FOR_ELEMENTS;
        if ($constantScheme->isFinal) $_return .= "final ";
        $_return .= "{$constantScheme->view->value} ";
        $_return .= "const {$constantScheme->name} = ";
        $_return .= "{$constantScheme->getValuePhpCode()};\n";

        return $_return;
    }
}
