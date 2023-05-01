<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader;

use DraculAid\PhpMocker\Schemes\AttributeScheme;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\UseScheme;

/**
 * Используется для хранения временных результатов получения схем классов из PHP кода в {@see PhpReader} и дочерних классов
 *
 * Оглавление:
 * @see self::$namespace - Текущее читаемое пространство имен
 * @see self::$uses - Массив со списком конструкций use для используемых классов, функций или констант
 * @see self::$schemeClass - Схема текущего читаемого класса или NULL, если чтение происходит вне класса
 * @see self::$codeBlockDeep - Указывает на текущую глубину вложений в фигурные скобки
 * @see self::$codeBlockForClassDeep - Уровень вложенности фигурных скобок с которого начато чтение кода класса
 * @see self::$attributes - Массив схем накопленных атрибутов
 */
class TmpResult
{
    /**
     * Текущее читаемое пространство имен
     */
    public string $namespace = '';

    /**
     * Список конструкций USE для используемых классов, функций или констант
     *
     * @var UseScheme[] $uses
     */
    public array $uses = [];

    /**
     * Схема текущего читаемого класса или NULL, если чтение происходит вне класса
     */
    public null|ClassScheme $schemeClass = null;

    /**
     * Указывает на текущую глубину вложений в фигурные скобки
     *
     * Некоторые значения:
     *   * 0 - нет чтения кода
     *   * 1 - идет чтение кода внутри класса, открыты фигурные скобки класса
     */
    public int $codeBlockDeep = 0;

    /**
     * Уровень вложенности фигурных скобок с которого начато чтение кода класса
     */
    public int $codeBlockForClassDeep = 1;

    /**
     * Список схем накопленных атрибутов
     *
     * @var AttributeScheme[] $attributes
     */
    public array $attributes = [];
}
