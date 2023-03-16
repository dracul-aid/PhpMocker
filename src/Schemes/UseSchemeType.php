<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Schemes;

use DraculAid\PhpMocker\Tools\AbstractEnums;

/**
 * Схемы для ООП элементов: Типы конструкций use (для классов, функций или констант) @see UseScheme
 *
 * Оглавление:
 * @see UseSchemeType::CLASSES() - для классов
 * @see UseSchemeType::CONSTANTS() - для констант
 * @see UseSchemeType::FUNCTIONS() - для функций
 */
class UseSchemeType extends AbstractEnums
{
    public static function CLASSES() {return static::createStringVariant('');}
    public static function CONSTANTS() {return static::createStringVariant('const');}
    public static function FUNCTIONS() {return static::createStringVariant('function');}
}
