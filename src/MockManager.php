<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker;

use DraculAid\PhpMocker\Exceptions\Managers\ClassManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\Managers\MethodManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\Managers\ObjectManagerNotFoundException;
use DraculAid\PhpMocker\Exceptions\PhpMockerLogicException;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodCase;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;

/**
 * Класс, для получения менеджеров мок-классов и мок-объектов
 *
 * Оглавление:
 * @see MockManager::getForClass() - Вернет "менеджер мок-класса" по имени мок-класса
 * @see MockManager::getForObject() - Вернет "менеджер мок-объекта" для мок-объекта
 * @see MockManager::getForMethod() - Вернет "менеджер мок-метода"
 * @see MockManager::getForMethodCase() - Вернет кейс вызова для мок-метода
 */
class MockManager
{
    /**
     * Вернет "менеджер мок-класса" по имени мок-класса
     *
     * @param   string   $mockClass   Полное имя мок-класса
     *
     * @return  ClassManager
     *
     * @throws  ClassManagerNotFoundException  В случае, если не был найден менеджер (обычно это значит, что был запрошен менеджер НЕ ДЛЯ мок-класса)
     */
    public static function getForClass(string $mockClass): ClassManager
    {
        return ClassManager::getManager($mockClass, true);
    }

    /**
     * Вернет "менеджер мок-объекта" для мок-объекта
     *
     * @param   object   $mockObject    Мок-объект, для которого ищется менеджер
     *
     * @return  ObjectManager
     *
     * @throws  ObjectManagerNotFoundException  В случае, если не был найден менеджер
     */
    public static function getForObject(object $mockObject): ObjectManager
    {
        return ObjectManager::getManager($mockObject, true);
    }

    /**
     * Вернет "менеджер мок-метода"
     *
     * @param   string|object   $mockClassOrObject   Мок-класс или Мок-объект
     * @param   string          $methodName          Имя метода
     *
     * @return  MethodManager
     *
     * @throws  ClassManagerNotFoundException    В случае, если не был найден "менеджер мок-класса"
     * @throws  ObjectManagerNotFoundException   В случае, если не был найден "менеджер мок-объекта"
     * @throws  MethodManagerNotFoundException   Если метод не определён в классе
     */
    public static function getForMethod($mockClassOrObject, string $methodName): MethodManager
    {
        if (!is_string($mockClassOrObject) && !is_object($mockClassOrObject)) throw new \TypeError('$mockClassOrObject is not string or object');

        if (is_string($mockClassOrObject)) return self::getForClass($mockClassOrObject)->getMethodManager($methodName);
        else return self::getForObject($mockClassOrObject)->getMethodManager($methodName);
    }

    /**
     * Вернет кейс вызова для мок-метода
     *
     * @param   string|object   $mockClassOrObject   Мок-класс или Мок-объект
     * @param   string          $methodName          Имя метода
     * @param   null|array      $arguments           Аргументы кейса вызова или NULL для "кейса по умолчанию"
     *
     * @return  MethodCase
     *
     * @throws  ClassManagerNotFoundException    В случае, если не был найден "менеджер мок-класса"
     * @throws  ObjectManagerNotFoundException   В случае, если не был найден "менеджер мок-объекта"
     * @throws  MethodManagerNotFoundException   Если метод не определён в классе
     * @throws  PhpMockerLogicException          Если метод не может использоваться как "мок-метод"
     */
    public static function getForMethodCase($mockClassOrObject, string $methodName, ?array $arguments = null): MethodCase
    {
        if (!is_string($mockClassOrObject) && !is_object($mockClassOrObject)) throw new \TypeError('$mockClassOrObject is not string or object');

        if ($arguments === null) return self::getForMethod($mockClassOrObject, $methodName)->defaultCase();
        else return self::getForMethod($mockClassOrObject, $methodName)->case($arguments);
    }
}
