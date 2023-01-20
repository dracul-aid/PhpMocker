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
 * Схемы для ООП элементов: описание аргументов методов
 *
 * Оглавление:
 * @see self::$name - Имя аргумента
 * @see self::$type - Тип аргумента
 * @see self::$isValue - Элемент имеет значение по умолчанию (и является необязательным)
 * @see self::$value - Значение аргумента по умолчанию
 * @see self::$valueFromConstant - Значение по умолчанию будет взято из константы (имя константы)
 * @see self::$isLink - Этот параметр передается по ссылке
 * @see self::$isVariadic - Аргумент поддерживает список значений
 * @see self::$attributes - Список атрибутов
 * @see self::ifInConstructGetPropertiesScheme() Вернет схему "свойства класса", если аргумент является аргументом конструктора и одновременно свойством класса
 */
class MethodArgumentScheme implements SchemeWithAttributesInterface
{
    /**
     * Имя элемента
     */
    public string $name;

    /**
     * Значение аргумента по умолчанию
     */
    public mixed $value = '';

    /**
     * Значение по умолчанию будет взято из константы (имя константы)
     */
    public string $valueFromConstant = '';

    /**
     * Тип данных
     */
    public string $type = '';

    /**
     * Элемент имеет значение по умолчанию (и является необязательным)
     */
    public bool $isValue = false;

    /**
     * Этот параметр передается по ссылке
     */
    public bool $isLink = false;

    /**
     * Аргумент поддерживает список значений
     */
    public bool $isVariadic = false;

    /**
     * Объект "схема метода" для которой создан аргумент
     */
    protected MethodScheme $methodScheme;

    /**
     * Список атрибутов
     *
     * Представляет собой массив строк, каждая строка - строковое представление атрибута
     *
     * @var AttributeScheme[] $attributes
     */
    public array $attributes = [];

    /**
     * Создание новой константы для схемы
     *
     * @param   MethodScheme   $methodScheme   Объект "схема класса" для которой создана константа
     * @param   string    $name           Имя элемента
     */
    public function __construct(MethodScheme $methodScheme, string $name)
    {
        $this->name = $name;
        $this->methodScheme = $methodScheme;
    }

    /**
     * Вернет схему "свойства класса", если аргумент является аргументом конструктора и одновременно свойством класса
     * Или NULL в противном случае
     *
     * @return  null|PropertyScheme
     */
    public function ifInConstructGetPropertiesScheme(): null|PropertyScheme
    {
        return match (true) {
            !isset($this->methodScheme->getClassScheme()->properties[$this->name]) => null,
            $this->methodScheme->getClassScheme()->properties[$this->name]->isInConstruct => $this->methodScheme->getClassScheme()->properties[$this->name],
            default => null
        };
    }
}
