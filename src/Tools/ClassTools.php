<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Tools;

/**
 * Статический класс с функциями, для работы с классами
 *
 * Оглавление
 * @see ClassTools::isLoad() - Проверит, данное имя является загруженным классом, трейтом, перечислением или интерфейсом
 * @see ClassTools::isInternal() - Проверит, является ли указанный класс встроенным в PHP классом
 * @see ClassTools::getMethodArgumentNames() - Вернет массив с именами всех аргументов метода
 */
class ClassTools
{
    /**
     * Проверит, данное имя является загруженным классом, трейтом, перечислением или интерфейсом
     * (т.е. загружен класс или нет)
     *
     * @param   string   $className   Имя класса любого типа
     *
     * @return  bool
     */
    public static function isLoad(string $className): bool
    {
        return class_exists($className, false)
            || interface_exists($className, false)
            || trait_exists($className, false)
            || enum_exists($className, false);
    }

    /**
     * Проверит, является ли указанный класс встроенным в PHP классом
     *
     * @param   string   $className   Имя класса любого типа
     *
     * @return  bool
     *
     * @throws  \ReflectionException   Если не удалось получить рефлексию для класса
     */
    public static function isInternal(string $className): bool
    {
        if (!self::isLoad($className)) return false;

        return (new \ReflectionClass($className))->isInternal();
    }

    /**
     * Вернет массив с именами всех аргументов метода
     *
     * @param   string|object   $classOrObject   Объект или класс, которому принадлежит метод
     * @param   string          $methodName      Имя метода
     *
     * @return  string[]  Вернет массив, в котором ключи и значения - имена аргументов
     *
     * @throws  \ReflectionException
     */
    public static function getMethodArgumentNames(string|object $classOrObject, string $methodName): array
    {
        /**
         * Кеш, для хранения списка полученных имен аргументов
         *    Ключ: полное имя метода (формат: class::method)
         *    Значение: массив с именами методов (ключи и значения - имена методов)
         */
        static $_storage = [];

        $fullMethodName = (is_string($classOrObject) ? $classOrObject : $classOrObject::class) . "::{$methodName}";

        // * * *

        if (isset($_storage[$fullMethodName]))
        {
            return $_storage[$fullMethodName];
        }
        else
        {
            $_storage[$fullMethodName] = [];

            foreach ((new \ReflectionMethod($classOrObject, $methodName))->getParameters() as $reflectionParameter)
            {
                $_storage[$fullMethodName][$reflectionParameter->getName()] = $reflectionParameter->getName();
            }

            return $_storage[$fullMethodName];
        }
    }
}
