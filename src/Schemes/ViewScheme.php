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
 * Схемы для ООП элементов: типы видимости
 *
 * Оглавление:
 * @see ViewScheme::PUBLIC()() - для публичных методов, свойств и констант
 * @see ViewScheme::PROTECTED()() - для protected методов, свойств и констант
 * @see ViewScheme::PRIVATE()() - для private методов, свойств и констант
 * @see ViewScheme::createFromReflection() - Вернет тип модификатора видимости по рефлексии
 */
class ViewScheme extends AbstractEnums
{
    public static function PUBLIC() {return static::createStringVariant('public');}
    public static function PROTECTED() {return static::createStringVariant('protected');}
    public static function PRIVATE() {return static::createStringVariant('private');}

    /**
     * Вернет тип модификатора видимости по рефлексии
     *
     * @param   \ReflectionProperty|\ReflectionMethod|\ReflectionClassConstant   $reflection    Объект рефлексия свойств, методов или констант классов
     *
     * @return  static
     */
    public static function createFromReflection(object $reflection): self
    {
        if ($reflection->isProtected()) return self::PROTECTED();
        elseif ($reflection->isPrivate()) return self::PRIVATE();
        else return self::PUBLIC();
    }
}
