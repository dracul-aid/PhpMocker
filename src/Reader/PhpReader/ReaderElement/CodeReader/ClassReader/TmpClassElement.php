<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader\PhpReader\ReaderElement\CodeReader\ClassReader;

use DraculAid\PhpMocker\Schemes\AbstractElementsScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;

/**
 * Используется для хранения временных результатов при чтении элементов класса "класса создание схемы класса на основе PHP кода"
 *
 * Оглавление:
 * --- Константы, определяющие, для какого свойства схемы элемента класса в данный момент идет чтение символов
 * @see TmpClassElement::WRITE_FOR_NAME - для "имени"
 * @see TmpClassElement::WRITE_FOR_VALUE - для "значения" (содержимого функций)
 * @see TmpClassElement::WRITE_FOR_METHOD_ARGUMENTS - для "аргументов функции"
 * @see TmpClassElement::WRITE_FOR_METHOD_RETURN - для "возвращаемого значения функции"
 * --- Свойства текущего чтения
 * @see self::$scheme - Хранит объект схему, для читаемого элемента класса (метода, константы...)
 * @see self::$view - Область видимости читаемого элемента
 * @see self::$writeFor - Для какого свойства схемы элемента класса в данный момент идет запись
 * @see self::$isStatic - Является ли читаемый элемент "статичным"
 * @see self::$isFinal - Является ли читаемый элемент "финальным"
 * @see self::$isAbstract - Является ли читаемый элемент "абстрактным"
 * @see self::$isReadonly - Является ли читаемый элемент "только для чтения"
 * @see self::clear() - Очищает данные накопленные для элемента
 * @see self::getView() - Вернет уровень видимости для элемента
 */
class TmpClassElement
{
    /**
     * В настоящий момент чтение проводится для "имени"
     *
     * Обслуживает @see TmpClassElement::$writeFor
     *
     * @see \DraculAid\PhpMocker\Schemes\ConstantScheme::$name - Если записывается имя константы
     * @see \DraculAid\PhpMocker\Schemes\PropertyScheme::$name - Если записывается имя константы
     * @see \DraculAid\PhpMocker\Schemes\MethodScheme::$name - Если записывается имя константы
     */
    public const WRITE_FOR_NAME = 1;
    /**
     * В настоящий момент чтение проводится для "значения" (содержимого функций)
     *
     * Обслуживает @see TmpClassElement::$writeFor
     *
     * @see \DraculAid\PhpMocker\Schemes\ConstantScheme::$innerPhpCode - Если записывается значение константы
     * @see \DraculAid\PhpMocker\Schemes\PropertyScheme::$innerPhpCode - Если записывается значение свойства
     * @see \DraculAid\PhpMocker\Schemes\MethodScheme::$innerPhpCode - Если записывается тело метода (кон метода)
     */
    public const WRITE_FOR_VALUE = 2;
    /**
     * В настоящий момент чтение проводится для "возвращаемого значения функции"
     * @see TmpClassElement::WRITE_FOR_METHOD_RETURN
     *
     * Обслуживает @see TmpClassElement::$writeFor
     */
    public const WRITE_FOR_METHOD_RETURN = 3;
    /**
     * В настоящий момент чтение проводится для "аргументов функции"
     * @see TmpClassElement::WRITE_FOR_METHOD_ARGUMENTS
     *
     * Обслуживает @see TmpClassElement::$writeFor
     */
    public const WRITE_FOR_METHOD_ARGUMENTS = 4;

    /**
     * Хранит объект схему, для читаемого элемента класса (метода, константы...)
     * @see TmpClassElement::$scheme
     *
     * @var null|\DraculAid\PhpMocker\Schemes\ConstantScheme|\DraculAid\PhpMocker\Schemes\PropertyScheme|\DraculAid\PhpMocker\Schemes\MethodScheme $scheme
     * (Классы перечислены явно, так как имеют немного разный набор методов)
     */
    public null|AbstractElementsScheme $scheme = null;

    /**
     * Область видимости читаемого элемента
     */
    public null|ViewScheme $view = null;
    /**
     * Является ли читаемый элемент "финальным"
     */
    public bool $isFinal = false;
    /**
     * Является ли читаемый элемент "абстрактным"
     */
    public bool $isAbstract  = false;
    /**
     * Является ли читаемый элемент "статичным"
     */
    public bool $isStatic  = false;
    /**
     * Является ли читаемый элемент "только для чтения"
     */
    public bool $isReadonly  = false;

    /**
     * Для какого свойства схемы элемента класса в данный момент идет запись
     * (0 - ни для чего особенного, или см константы TmpClassElement::WRITE_FOR_***)
     */
    public int $writeFor = 0;

    /**
     * Последнее неразобранное ключевое слово
     * (представляет собой тип данных свойства класса)
     */
    public string $endWord = ' ';

    /**
     * Очищает данные накопленные для элемента
     *
     * @return void
     */
    public function clear(): void
    {
        $this->scheme = null;
        $this->writeFor = 0;
        $this->endWord = ' ';
        $this->view = null;
        $this->isFinal = false;
        $this->isAbstract = false;
        $this->isReadonly = false;
        $this->isStatic = false;
    }

    /**
     * Вернет уровень видимости для элемента класса
     *
     * @return  ViewScheme   Если уровень видимости не был определен, вернет "публичный"
     */
    public function getView(): ViewScheme
    {
        return $this->view ?? ViewScheme::PUBLIC;
    }
}
