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
 * @see ConstantScheme::getClassScheme() - Вернет схему класса
 * --- Свойства
 * @see ConstantScheme::$name - Имя константы
 * @see ConstantScheme::$view - Уровень видимости (public, protected...)
 * @see ConstantScheme::$value - Значение
 * @see ConstantScheme::$innerPhpCode - Код значения константы
 * @see ConstantScheme::$isEnumCase - Является ли константа вариантом перечисления
 * @see ConstantScheme::$isFinal - Константа является финальным (от него невозможно создавать потомки)
 * @see ConstantScheme::$isDefine - Константа определена (переопределена) в этом текущем классе схема
 * @see ConstantScheme::$attributes - Список атрибутов
 * @see ConstantScheme::isPublic() - Проверит, является ли константа public
 * @see ConstantScheme::isProtected() - Проверит, является ли константа protected
 * @see ConstantScheme::isPrivate() - Проверит, является ли константа private
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
