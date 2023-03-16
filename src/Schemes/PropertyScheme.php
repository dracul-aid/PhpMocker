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
 * Схемы для ООП элементов: описание свойств класса
 *
 * Оглавление:
 * @see self::getClassScheme() - Вернет схему класса
 * --- Свойства
 * @see self::$name - Имя свойства
 * @see self::$view - Уровень видимости (public, protected...)
 * @see self::$type - Тип данных
 * @see self::$isValue - Имеет значение
 * @see self::$value - Значение
 * @see self::$valueFromConstant - Значение будет взято из константы (имя константы)
 * @see self::$innerPhpCode - Значение представляет собой PHP код
 * @see self::$isReadonly - Только для чтения
 * @see self::$isDefine - Свойство определено (переопределено) в этом текущем классе схемы
 * @see self::$isStatic - Статическое или нет
 * @see self::$isInConstruct - Определен в конструкторе
 * @see self::$attributes - Список атрибутов
 * @see self::isPublic() - Проверит, является ли свойство public
 * @see self::isProtected() - Проверит, является ли свойство protected
 * @see self::isPrivate() - Проверит, является ли свойство private
 * --- Для облегчения работы со значениями
 * @see self::setValue() - Установит значение для свойства
 * @see self::clearValue() - Установит, что у свойства нет значения
 * @see self::getValuePhpCode() - Вернет PHP код с значением свойства или NULL - если свойство не имеет значения
 */
class PropertyScheme extends AbstractElementsScheme
{
    /**
     * Имеет значение
     */
    public bool $isValue = false;

    /**
     * Значение
     */
    public $value = '';

    /**
     * Значение будет взято из константы (имя константы)
     */
    public string $valueFromConstant = '';

    /**
     * Тип данных
     */
    public string $type = '';

    /**
     * Элемент является статическим или нет
     */
    public bool $isStatic = false;

    /**
     * Элемент доступен только для чтения
     */
    public bool $isReadonly = false;

    /**
     * Определен в конструкторе
     */
    public bool $isInConstruct = false;

    /**
     * Установит значение для свойства
     *
     * @param   mixed    $value    Устанавливаемое значение
     *
     * @return  $this
     */
    public function setValue($value): self
    {
        $this->isValue = true;
        $this->value = $value;

        return $this;
    }

    /**
     * Установит, что у свойства нет значения
     *
     * @return  $this
     */
    public function clearValue(): self
    {
        $this->isValue = false;
        $this->value = null;

        return $this;
    }

    /**
     * Вернет PHP код со значением свойства или NULL - если свойство не имеет значения
     *
     * @return  null|string
     */
    public function getValuePhpCode(): ?string
    {
        // если свойство не имеет значения
        if (!$this->isValue)
        {
            return null;
        }

        // * * *

        // если есть явно указанный PHP код
        if ($this->innerPhpCode !== '') return $this->innerPhpCode;
        // если есть имя константы
        elseif ($this->valueFromConstant !== '') return $this->valueFromConstant;
        // в остальных случаях значение
        else return var_export($this->value, true);
    }
}
