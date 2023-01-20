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
 * Схемы для ООП элементов: Типы конструкций use (для классов, функций или констант)
 *
 * Оглавление:
 * @see self::$type - Тип конструкции (т.е. она для класса, константы или функции)
 * @see self::$namespace - Пространство имен (пустая строка для глобального пространства имен)
 * @see self::$name - Имя элемента
 * @see self::$alias - Если элемент указан с псевдонимом (конструкция name AS alias)
 * @see self::getFullName() - Вернет полное имя
 * @see self::generatePhpCode() - Сгенерирует PHP код для конструкции use
 */
class UseScheme
{
    /**
     * Тип конструкции (т.е. она для класса, константы или функции)
     */
    public UseSchemeType $type;

    /**
     * Пространство имен (пустая строка для глобального пространства имен)
     */
    public string $namespace = '';

    /**
     * Имя элемента
     */
    public string $name = '';

    /**
     * Если элемент указан с псевдонимом (конструкция name AS alias)
     * Пустая строка - если псевдонима нет
     */
    public string $alias = '';

    /**
     * @param   UseSchemeType   $type   Тип use
     */
    public function __construct(UseSchemeType $type)
    {
        $this->type = $type;
    }

    /**
     * Вернет полное имя элемента (т.е. включая пространство имен)
     *
     * @return string
     */
    public function getFullName(): string
    {
        return ($this->namespace === '' ? '' : "{$this->namespace}\\") . $this->name;
    }

    /**
     * Вернет полное имя элемента (т.е. включая пространство имен) с псевдонимом, если он есть
     *
     * @return string
     */
    public function getFullNameWithAlias(): string
    {
        $_return = $this->getFullName();
        if ($this->alias !== '') $_return .= " as {$this->alias}";

        return $_return;
    }

    /**
     * Сгенерирует PHP код для конструкции use
     *
     * @return string
     */
    public function generatePhpCode(): string
    {
        $_return = 'use ';
        if ($this->type->value !== '') $_return .= "{$this->type->value} ";

        return $_return . $this->getFullNameWithAlias() . ';';
    }
}
