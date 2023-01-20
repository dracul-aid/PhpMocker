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
 * Схемы для ООП элементов: описание методов класса
 *
 * Оглавление:
 * @see MethodScheme::getClassScheme() - Вернет схему класса
 * --- Свойства
 * @see MethodScheme::$name - Имя метода
 * @see MethodScheme::$view - Уровень видимости (public, protected...)
 * @see MethodScheme::$returnType - Тип возвращаемых данных
 * @see MethodScheme::$isReturnLink - Метод возвращает значение по ссылке
 * @see MethodScheme::$isFinal - Элемент является финальным (от него невозможно создавать потомки)
 * @see MethodScheme::$isDefine - Свойство определено (переопределено) в этом текущем классе схемы
 * @see MethodScheme::$isStatic - Статическое или нет
 * @see MethodScheme::$isAbstract - Абстрактный метод
 * @see MethodScheme::$isAnonymous - Анонимная функция или нет
 * @see MethodScheme::$innerPhpCode - Код тела функции
 * @see MethodScheme::$attributes - Список атрибутов
 * @see MethodScheme::$argumentsPhpCode - Список аргументов в виде строки PHP кода
 * @see MethodScheme::$arguments - Список аргументов
 * @see MethodScheme::isPublic() - Проверит, является ли метод public
 * @see MethodScheme::isProtected() - Проверит, является ли метод protected
 * @see MethodScheme::isPrivate() - Проверит, является ли метод private
 * @see MethodScheme::canReturnValues() - Вернет указание, что функция может вернуть какой-то результат.
 */
class MethodScheme extends AbstractElementsScheme
{
    /**
     * Тип возвращаемых данных
     */
    public string $returnType = '';

    /**
     * Анонимная функция или нет (замыкание)
     */
    public bool $isAnonymous = false;

    /**
     * Элемент является статическим или нет
     */
    public bool $isStatic = false;

    /**
     * Абстрактный метод
     */
    public bool $isAbstract = false;

    /**
     * Элемент является финальным (от него невозможно создавать потомки)
     */
    public bool $isFinal = false;

    /**
     * Метод возвращает значение по ссылке
     */
    public bool $isReturnLink = false;

    /**
     * Список аргументов
     *
     * Представляет собой массив:
     *    * индекс: имя аргумента
     *    * значение: объект - схема аргумента
     *
     * @var MethodArgumentScheme[] $arguments
     */
    public array $arguments = [];
    /**
     * Список аргументов в виде строки PHP кода
     */
    public string $argumentsPhpCode = '';

    /**
     * Вернет указание, что функция может вернуть какой-то результат.
     * (Функции, с типом ответа "void" или "never" не могут возвращать данные)
     *
     * @return  bool
     */
    public function canReturnValues(): bool
    {
        return $this->returnType !== 'void' && $this->returnType !== 'never';
    }
}
