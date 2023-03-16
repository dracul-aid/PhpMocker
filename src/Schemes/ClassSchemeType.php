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
 * Схемы для ООП элементов: типы классов (класс, перечисление, интерфейс...)
 *
 * Оглавление:
 * --- Типы
 * @see ClassSchemeType::CLASSES() - Обычные классы
 * @see ClassSchemeType::ABSTRACT_CLASSES() - Абстрактные классы
 * @see ClassSchemeType::INTERFACES() - Интерфейсы
 * @see ClassSchemeType::TRAITS() - Трейты
 * @see ClassSchemeType::ENUMS() - Перечисления
 * @see ClassSchemeType::createFromReflection() - Вернет тип по данным полученным из рефлексии класса
 * --- Получение информации по типам
 * @see self::canUseFinal() - Может ли использоваться с ключевым словом final
 * @see self::canUseReadonly() - Может ли использоваться с ключевым словом readonly
 * @see self::canUseExtends() - Может ли использовать классы-родители
 * @see self::canUseProperties() - Может ли использовать свойства
 * @see self::canUseReadonly() - Может ли использовать свойство readonly
 */
class ClassSchemeType extends AbstractEnums
{
    public static function CLASSES() {return static::createStringVariant('class');}
    public static function ABSTRACT_CLASSES() {return static::createStringVariant('abstract class');}
    public static function INTERFACES() {return static::createStringVariant('interface');}
    public static function TRAITS() {return static::createStringVariant('trait');}
    public static function ENUMS() {return static::createStringVariant('enum');}

    public static function from(string $variant): self
    {
        if ($variant === 'class') return self::CLASSES();
        elseif ($variant === 'abstract class') return self::ABSTRACT_CLASSES();
        elseif ($variant === 'interface') return self::INTERFACES();
        elseif ($variant === 'trait') return self::TRAITS();
        elseif ($variant === 'enum') return self::ENUMS();

        throw new \TypeError("Undefined Enum variant: {$variant}");
    }

    /**
     * Вернет тип по данным полученным из рефлексии класса
     *
     * @param   \ReflectionClass   $reflection   Рефлексия класса
     *
     * @return  static
     */
    public static function createFromReflection(\ReflectionClass $reflection): self
    {
        if ($reflection->isInterface()) return self::INTERFACES();
        elseif ($reflection->isTrait()) return self::TRAITS();
        elseif (PHP_MAJOR_VERSION > 7 && $reflection->isEnum()) return self::ENUMS();
        elseif ($reflection->isAbstract()) return self::ABSTRACT_CLASSES();
        else return self::CLASSES();
    }

    /**
     * Вернет указание, может ли данный тип класса использоваться с ключевым словом final
     *
     * @return   bool  TRUE если может использоваться с ключевым словом final
     */
    public function canUseFinal(): bool
    {
        return $this === self::CLASSES();
    }

    /**
     * Вернет указание, может ли данный тип класса использоваться интерфейсы
     *
     * @return   bool  TRUE если может использоваться интерфейсы в implements
     */
    public function canUseInterfaces(): bool
    {
        return $this !== self::TRAITS();
    }

    /**
     * Вернет указание, может ли данный тип класса использовать классы-родители
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseExtends(): bool
    {
        return $this === self::CLASSES() || $this === self::ABSTRACT_CLASSES();
    }

    /**
     * Вернет указание, может ли данный тип класса использоваться с ключевым словом readonly
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseReadonly(): bool
    {
        if (PHP_MAJOR_VERSION < 8 || PHP_MINOR_VERSION < 2) return false;
        else return $this === self::CLASSES() || $this === self::ABSTRACT_CLASSES();
    }

    /**
     * Вернет указание, может ли данный тип класса использовать свойства
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseProperties(): bool
    {
        if ($this === self::CLASSES() || $this === self::ABSTRACT_CLASSES() || $this === self::TRAITS()) return true;
        else return false;
    }

    /**
     * Вернет указание, может ли данный тип класса использовать абстрактные методы
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseAbstractMethods(): bool
    {
        return $this === self::ABSTRACT_CLASSES() || $this === self::TRAITS();
    }
}
