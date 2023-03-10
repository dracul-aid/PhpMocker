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
 * Схемы для ООП элементов: описание класса
 *
 * Оглавление:
 * --- Имя класса
 * @see ClassScheme::$name - Имя класса
 * @see ClassScheme::$namespace - Пространство имен
 * @see ClassScheme::full_name() - Вернет полное имя класса (т.е. включая пространство имен)
 * --- Параметры класса
 * @see ClassScheme::$isReadonly - Элемент доступен только для чтения
 * @see ClassScheme::$isFinal - Элемент является финальным (от него невозможно создавать потомки)
 * @see ClassScheme::$isAnonymous - Анонимный или нет класс
 * @see ClassScheme::$isInternal - Это встроенный класс или нет
 * @see ClassScheme::$enumType - Хранит тип данных для варианта перечисления
 * @see ClassScheme::$type - Тип класса (класс, интерфейс, трейт или перечисление)
 * @see ClassScheme::$parent - Имя класса-родителя (пустая строка, если такового нет)
 * --- Вложенные сущности класса
 * @see ClassScheme::$innerPhpCode - Внутренний код элемента класса (без трейтов)
 * @see ClassScheme::$attributes - Список атрибутов
 * @see ClassScheme::$interfaces - Список интерфейсов
 * @see ClassScheme::$traits - Список трейтов
 * @see ClassScheme::$traitsPhpCode - PHP код выводимый на месте вызова трейтов
 * @see ClassScheme::$constants - Список всех констант (включая "варианты перечислений")
 * @see ClassScheme::$properties - Список всех свойств
 * @see ClassScheme::$methods - Список всех методов
 * @see ClassScheme::$uses - Список всех используемых use (для классов, функций и констант)
 * @see ClassScheme::getConstructor() - Вернет схему конструктора класса или NULL, если конструктор отсутствует
 */
class ClassScheme extends AbstractBasicScheme
{
    /**
     * Пространство имен
     */
    public string $namespace = '';

    /**
     * Тип класса (класс, интерфейс, трейт или перечисление)
     */
    public ClassSchemeType $type;

    /**
     * Элемент является финальным (от него невозможно создавать потомки)
     */
    public bool $isFinal = false;

    /**
     * Элемент доступен только для чтения
     */
    public bool $isReadonly = false;

    /**
     * Анонимный или нет класс
     */
    public bool $isAnonymous = false;

    /**
     * Это встроенный класс или нет
     */
    public bool $isInternal = false;

    /**
     * Хранит тип данных для варианта перечисления
     * (актуально, если схема класса принадлежит перечислению)
     */
    public string $enumType = '';

    /**
     * Имя класса-родителя (пустая строка, если такового нет)
     */
    public string $parent = '';

    /**
     * Список интерфейсов
     *
     * @var string[] $interfaces
     */
    public array $interfaces = [];

    /**
     * Список трейтов
     *
     * @var string[] $traits
     *
     * Все перечисленные трейты используются как "use trait_name;" - т.е. используясь полностью
     */
    public array $traits = [];
    /**
     * PHP код выводимый на месте вызова трейтов
     */
    public string $traitsPhpCode = '';

    /**
     * Список всех констант (включая "варианты перечислений")
     *
     * Представляет собой массив:
     *    * индекс: имя константы
     *    * значение: объект - схема константы
     *
     * @var ConstantScheme[] $constants
     */
    public array $constants = [];

    /**
     * Список всех свойств
     *
     * Представляет собой массив:
     *    * индекс: имя свойства
     *    * значение: объект - схема свойства
     *
     * @var PropertyScheme[] $properties
     */
    public array $properties = [];

    /**
     * Список всех методов
     *
     * Представляет собой массив:
     *    * индекс: имя метода
     *    * значение: объект - схема метода
     *
     * @var MethodScheme[] $methods
     */
    public array $methods = [];

    /**
     * Список всех используемых use (для классов, функций и констант)
     *
     * @var UseScheme[] $uses
     */
    public array $uses = [];

    /**
     * Создание схемы класса
     *
     * @param   ClassSchemeType    $type    Тип создаваемого класса (класс, трейт, интерфейс, перечисление)
     * @param   string         $name    Полное имя класса (включая пространство имен)
     */
    public function __construct(ClassSchemeType $type, string $name)
    {
        $this->type = $type;
        $this->setFullName($name);
    }

    /**
     * Вернет полное имя класса (т.е. включая пространство имен)
     *
     * @return string
     */
    public function getFullName(): string
    {
        return ($this->namespace === '' ? '' : "{$this->namespace}\\") . $this->name;
    }

    /**
     * Вернет полное имя класса (т.е. включая пространство имен)
     *
     * @param   string    $name    Полное имя класса
     *
     * @return  $this
     */
    public function setFullName(string $name): static
    {
        if ($name === '')
        {
            $this->namespace = '';
            $this->name = '';
            return $this;
        }

        // * * *

        if ($name[0] === '\\') $name = substr($name, 1);
        $this->name = basename($name);

        $this->namespace = dirname($name);
        if ($this->namespace === '.') $this->namespace = '';

        return $this;
    }

    /**
     * Вернет схему конструктора класса или NULL, если конструктор отсутствует
     *
     * @return   null|MethodScheme   Вернет схему метода-конструктора или NULL, если конструктор не описан
     */
    public function getConstructor(): null|MethodScheme
    {
        return $this->methods['__construct'] ?? null;
    }
}
