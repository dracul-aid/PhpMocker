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
 * @see PropertyScheme::getClassScheme() - Вернет схему класса
 * --- Свойства
 * @see PropertyScheme::$name - Имя свойства
 * @see PropertyScheme::$view - Уровень видимости (public, protected...)
 * @see PropertyScheme::$type - Тип данных
 * @see PropertyScheme::$isValue - Имеет значение
 * @see PropertyScheme::$value - Значение
 * @see PropertyScheme::$valueFromConstant - Значение будет взято из константы (имя константы)
 * @see PropertyScheme::$innerPhpCode - Значение представляет собой PHP код
 * @see PropertyScheme::$isReadonly - Только для чтения
 * @see PropertyScheme::$isDefine - Свойство определено (переопределено) в этом текущем классе схемы
 * @see PropertyScheme::$isStatic - Статическое или нет
 * @see PropertyScheme::$isInConstruct - Определен в конструкторе
 * @see PropertyScheme::$attributes - Список атрибутов
 * @see PropertyScheme::isPublic() - Проверит, является ли свойство public
 * @see PropertyScheme::isProtected() - Проверит, является ли свойство protected
 * @see PropertyScheme::isPrivate() - Проверит, является ли свойство private
 * --- Для облегчения работы со значениями
 * @see PropertyScheme::setValue() - Установит значение для свойства
 * @see PropertyScheme::clearValue() - Установит, что у свойства нет значения
 * @see PropertyScheme::getValuePhpCode() - Вернет PHP код с значением свойства или NULL - если свойство не имеет значения
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
    public mixed $value = '';

    /**
     * Значение будет взято из константы (имя константы)
     */
    public mixed $valueFromConstant = '';

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
    public function setValue(mixed $value): static
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
    public function clearValue(): static
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
    public function getValuePhpCode(): null|string
    {
        // если свойство не имеет значения
        if (!$this->isValue)
        {
            return null;
        }

        // * * *

        return match (true) {
            // если есть явно указанный PHP код
            $this->innerPhpCode !== '' => $this->innerPhpCode,
            // если есть имя константы
            $this->valueFromConstant !== '' => $this->valueFromConstant,
            // в остальных случаях значение
            default => var_export($this->value, true),
        };
    }
}
