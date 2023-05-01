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

use DraculAid\PhpMocker\Schemes\AttributeScheme;
use DraculAid\PhpMocker\Schemes\AbstractBasicScheme;

/**
 * Создание PHP кода атрибутов, для класса или элементов классов
 *
 * Это класс, для разгрузки кода {@see ClassGenerator} - Генератора PHP кода класса по схеме класса
 * В настоящий момент (PHP 8.1), PHP поддерживает атрибуты только для классов, но не для элементов класса, поддержка
 * для элементов добавлена на будущее, так как со временем она может появиться
 *
 * @see AttributeScheme - Схема атрибута
 * @see AbstractBasicScheme - Абстрактный класс для схем, поддерживающих атрибуты
 *
 * Оглавление:
 * @see AttributesGenerator::exe() - Создает PHP код с атрибутами
 */
class AttributesGenerator
{
    /**
     * Создает PHP код с атрибутами для указанной схемы класса или элемента класса
     *
     * @param   AbstractBasicScheme   $scheme   Схема класса или элемента класса, для которой создаются атрибуты
     *
     * @return  string
     */
    public static function exe(AbstractBasicScheme $scheme): string
    {
        $_return = '';

        // пройдем по списку атрибутов
        foreach ($scheme->attributes as $attribute)
        {
            $_return .= ClassGenerator::NEW_LINE_FOR_ELEMENTS;
            $_return .= '#[';

            $_return .= '\\' . $attribute->getFullName();

            if ($attribute->innerPhpCode !== '') $_return .= "({$attribute->innerPhpCode})";
            elseif (count($attribute->arguments) > 0) $_return .= '(' . var_export($attribute->arguments, true) . ')';

            $_return .= "]\n";
        }

        return $_return;
    }
}
