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

/**
 * Схемы для ООП элементов: типы классов (класс, перечисление, интерфейс...)
 *
 * Оглавление:
 * --- Типы
 * @see ClassSchemeType::CLASSES - Обычные классы
 * @see ClassSchemeType::ABSTRACT_CLASSES - Абстрактные классы
 * @see ClassSchemeType::INTERFACES - Интерфейсы
 * @see ClassSchemeType::TRAITS - Трейты
 * @see ClassSchemeType::ENUMS - Перечисления
 * @see ClassSchemeType::createFromReflection() - Вернет тип по данным полученным из рефлексии класса
 * --- Получение информации по типам
 * @see self::canUseFinal() - Может ли использоваться с ключевым словом final
 * @see self::canUseReadonly() - Может ли использоваться с ключевым словом readonly
 * @see self::canUseExtends() - Может ли использовать классы-родители
 * @see self::canUseProperties() - Может ли использовать свойства
 * @see self::canUseReadonly() - Может ли использовать свойство readonly
 */
enum ClassSchemeType: string
{
    case CLASSES = 'class';
    case ABSTRACT_CLASSES = 'abstract class';
    case INTERFACES = 'interface';
    case TRAITS = 'trait';
    case ENUMS = 'enum';

    /**
     * Вернет тип по данным полученным из рефлексии класса
     *
     * @param   \ReflectionClass   $reflection   Рефлексия класса
     *
     * @return  static
     */
    public static function createFromReflection(\ReflectionClass $reflection): self
    {
        return match (true) {
            $reflection->isInterface() => self::INTERFACES,
            $reflection->isTrait() => self::TRAITS,
            $reflection->isEnum() => self::ENUMS,
            $reflection->isAbstract() => self::ABSTRACT_CLASSES,
            default => self::CLASSES,
        };
    }

    /**
     * Вернет указание, может ли данный тип класса использоваться с ключевым словом final
     *
     * @return   bool  TRUE если может использоваться с ключевым словом final
     */
    public function canUseFinal(): bool
    {
        return $this === self::CLASSES;
    }

    /**
     * Вернет указание, может ли данный тип класса использоваться интерфейсы
     *
     * @return   bool  TRUE если может использоваться интерфейсы в implements
     */
    public function canUseInterfaces(): bool
    {
        return $this !== self::TRAITS;
    }

    /**
     * Вернет указание, может ли данный тип класса использовать классы-родители
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseExtends(): bool
    {
        return $this === self::CLASSES || $this === self::ABSTRACT_CLASSES;
    }

    /**
     * Вернет указание, может ли данный тип класса использоваться с ключевым словом readonly
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseReadonly(): bool
    {
        if (PHP_MAJOR_VERSION < 8 || PHP_MINOR_VERSION < 2) return false;
        else return $this === self::CLASSES || $this === self::ABSTRACT_CLASSES;
    }

    /**
     * Вернет указание, может ли данный тип класса использовать свойства
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseProperties(): bool
    {
        return match ($this) {
            self::CLASSES, self::ABSTRACT_CLASSES => true, self::TRAITS => true,
            default => false,
        };
    }

    /**
     * Вернет указание, может ли данный тип класса использовать абстрактные методы
     *
     * @return   bool  TRUE если может использоваться с ключевым словом readonly
     */
    public function canUseAbstractMethods(): bool
    {
        return $this === self::ABSTRACT_CLASSES || $this === self::TRAITS;
    }
}
