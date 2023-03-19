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
 * Схемы для ООП элементов: базовые элементы для классов, свойств, методов и констант
 *
 * Оглавление:
 * @see self::$name - Имя элемента
 * @see self::$innerPhpCode - Внутренний код элемента (PHP код)
 * @see self::$attributes - Список атрибутов
 */
abstract class AbstractBasicScheme implements SchemeWithAttributesInterface
{
    /**
     * Имя элемента
     */
    public string $name = '';

    /**
     * Внутренний код элемента (PHP код)
     *
     * Для класса - это содержимое класса (без трейтов)
     * Для констант и свойств - значение
     * Для методов - тело функции
     */
    public mixed $innerPhpCode = '';

    /**
     * Список атрибутов
     *
     * Представляет собой массив строк, каждая строка - строковое представление атрибута
     *
     * @var AttributeScheme[] $attributes
     */
    public array $attributes = [];
}
