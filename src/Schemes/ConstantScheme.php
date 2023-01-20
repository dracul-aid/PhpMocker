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
 * Схемы для ООП элементов: описание констант класса (включая "варианты перечислений")
 *
 * Оглавление:
 * @see self::getClassScheme() - Вернет схему класса
 * --- Свойства
 * @see self::$name - Имя константы
 * @see self::$view - Уровень видимости (public, protected...)
 * @see self::$value - Значение
 * @see self::$innerPhpCode - Код значения константы
 * @see self::$isEnumCase - Является ли константа вариантом перечисления
 * @see self::$isFinal - Константа является финальным (от него невозможно создавать потомки)
 * @see self::$isDefine - Константа определена (переопределена) в этом текущем классе схема
 * @see self::$attributes - Список атрибутов
 * @see self::isPublic() - Проверит, является ли константа public
 * @see self::isProtected() - Проверит, является ли константа protected
 * @see self::isPrivate() - Проверит, является ли константа private
 */
class ConstantScheme extends AbstractElementsScheme
{
    /**
     * Значение
     */
    public mixed $value = '';

    /**
     * Элемент является финальным (от него невозможно создавать потомки)
     */
    public bool $isFinal = false;

    /**
     * Является ли константа вариантом перечисления
     */
    public bool $isEnumCase = false;

    /**
     * @param   ClassScheme   $schemesClass   Объект "схема класса" для которой создана константа
     * @param   string    $name            Имя константы
     * @param   mixed     $value           Значение константы
     */
    public function __construct(ClassScheme $schemesClass, string $name, mixed $value = '')
    {
        parent::__construct($schemesClass, $name);
        $this->value = $value;
    }

    /**
     * Вернет PHP код со значением константы. Если константа - вариант перечисления без значения, то вернет пустую строку.
     *
     * @return  string
     */
    public function getValuePhpCode(): string
    {
        return match (true)
        {
            $this->innerPhpCode !== '' => $this->innerPhpCode,
            $this->isEnumCase => isset($this->value->value) ? var_export($this->value->value, true) : '',
            default => var_export($this->value, true),
        };
    }
}
