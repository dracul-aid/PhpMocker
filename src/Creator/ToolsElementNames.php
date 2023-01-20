<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator;

/**
 * Статический класс с инструментами для работы с мок классами и мок объектами
 *
 * Оглавление:
 * @see ToolsElementNames::mockName() - Вернет строку для использования в качестве имен мок элементов
 * @see ToolsElementNames::mockClassName() - Создаст имя для мок-класса
 * --- Имена методов для обращения к мок-объектам
 * @see ToolsElementNames::methodPropertyGet() - Получение значения свойства
 * @see ToolsElementNames::methodPropertySet() - Запись значения в свойство
 * @see ToolsElementNames::methodCall() - Вызов статического метода
 * --- Имена методов для обращения к мок-классам
 * @see ToolsElementNames::methodConstGet() - Получение значения константы
 * @see ToolsElementNames::methodStaticPropertyGet() - Получение значения статического свойства
 * @see ToolsElementNames::methodStaticPropertySet() - Запись значения в статическое свойство
 * @see ToolsElementNames::methodStaticCall() - Вызов статического метода
 */
class ToolsElementNames
{
    /**
     * Вернет строку для использования в качестве имен мок элементов
     *
     * @param   string   $prefix    Префикс
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с префиксом имен
     */
    public static function mockName(string $prefix, string $uniqid): string
    {
        return "___dracul_aid_mocker_{$prefix}_{$uniqid}___";
    }

    /**
     * Создаст имя для мок-класса
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с префиксом имен
     */
    public static function mockClassName(string $uniqid): string
    {
        return static::mockName('ClassRandomName', $uniqid);
    }

    /**
     * Вернет имя метода, возвращающего значение свойства из мок объекта (включая не публичные)
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с именем метода
     */
    public static function methodPropertyGet(string $uniqid): string
    {
        return static::namePrefix($uniqid) . 'object_get';
    }

    /**
     * Вернет имя метода, записывающего значение в свойство из мок объекта (включая не публичные)
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с именем метода
     */
    public static function methodPropertySet(string $uniqid): string
    {
        return static::namePrefix($uniqid) . 'object_set';
    }

    /**
     * Вернет имя метода, вызывающий метод из мок объекта (включая не публичные)
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с именем метода
     */
    public static function methodCall(string $uniqid): string
    {
        return static::namePrefix($uniqid) . 'object_call';
    }

    /**
     * Вернет имя метода, возвращающего значение константы из мок-объекта или мок-класса (включая не публичные)
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с именем метода
     */
    public static function methodConstGet(string $uniqid): string
    {
        return static::namePrefix($uniqid) . 'const';
    }

    /**
     * Вернет имя метода, возвращающего значение статического свойства из мок объекта или класса (включая не публичные)
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с именем метода
     */
    public static function methodStaticPropertyGet(string $uniqid): string
    {
        return static::namePrefix($uniqid) . 'class_get';
    }

    /**
     * Вернет имя метода, записывающего значение в статического свойство из мок объекта или класса (включая не публичные)
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с именем метода
     */
    public static function methodStaticPropertySet(string $uniqid): string
    {
        return static::namePrefix($uniqid) . 'class_set';
    }

    /**
     * Вернет имя метода, вызывающий статический метод из мок объекта или класса (включая не публичные)
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с именем метода
     */
    public static function methodStaticCall(string $uniqid): string
    {
        return static::namePrefix($uniqid) . 'class_call';
    }

    /**
     * Вернет префикс имен методов для мок-методов
     *
     * @param   string   $uniqid    Идентификатор уникальности
     *
     * @return  string   Вернет строку с префиксом имен
     */
    protected static function namePrefix(string $uniqid): string
    {
        return static::mockName('method', $uniqid);
    }
}
