<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers\Tools;

use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;

/**
 * Обработка вызова мок-метода
 *
 * Оглавление:
 * @see HasCalled::exeForMethod() - Отрабатывает вызов мок-метода
 * --- Свойства вызова
 * @see self::$function [readonly] - Имя вызванной функции (метода)
 * @see self::$arguments [readonly] - Аргументы, с которыми была вызвана функция
 * @see self::$ownerClass [readonly]  - Класс, в котором находится код вызванного мок-метода
 * @see self::$callClass [readonly]  - Класс, в котором произошел вызов
 * @see self::$callObject [readonly]  - Объект, в котором произошел вызов мок-метода
 * @see self::getArgumentValueByName() - Вернет аргумент вызова по его имени
 * @see self::getArgumentValueByNumber() - Вернет аргумент вызова по его номеру
 *
 * Свойства доступные только для чтения @see self::__get()
 * @property string $function
 * @property array|HasCalledArguments $arguments
 * @property string $ownerClass
 * @property string $callClass
 * @property null|object $callObject
 */
class HasCalled
{
    /**
     * Имя вызванной функции (метода)
     */
    public string $function;

    /**
     * Аргументы, с которыми была вызвана функция (Пустой массив - вызов был без аргументов)
     *
     * @var array|HasCalledArguments $arguments  Допускает работу с объектом-аргументов, как с массивом
     *    Ключи: имена аргументов
     *    Значения: ссылка на аргумент метода
     */
    public HasCalledArguments $arguments;

    /**
     * Класс, в котором находится код вызванного мок-метода
     */
    public string $ownerClass;

    /**
     * Класс, в котором произошел вызов
     * (напоминание: вызов методов трейтов, никогда не происходит в самом трейте)
     */
    public string $callClass;

    /**
     * Объект, в котором произошел вызов мок-метода
     * (NULL - для статических методов)
     */
    public ?object $callObject;

    public function __get(string $name)
    {
        return $this->{$name};
    }

    /**
     * Отрабатывает вызов мок-метода
     *
     * @param   string        $ownerClass    Класс, в котором находится код вызванного мок-метода
     * @param   string        $callClass     Класс, в котором произошел вызов
     * @param   null|object   $callObject    Объект, в котором произошел вызов мок-метода (NULL - для статических методов)
     * @param   string        $method        Имя метода
     * @param   array         $arguments     Аргументы с которыми был вызван метод
     *
     * @return  null|CallResult   Вернет объект "результат вызова мок-метода" или NULL если результат вернуть нельзя
     *
     * Вернет NULL, если:
     *    * Не был найден менеджер класса
     *    * Не был найден менеджер объекта
     *
     * @todo Подумать что делать с методами трейтов, которые были переименованы в классе (use traitName {oldName as newName}),
     * так как методы остаются доступными под обоими именами. Напоминалка: определить метод описанный в трейте, можно только через путь к файлу и номерам строк
     */
    public static function exeForMethod(string $ownerClass, string $callClass, ?object $callObject, string $method, array $arguments): ?CallResult
    {
        $call = new self();
        $call->ownerClass = $ownerClass;
        $call->callClass = $callClass;
        $call->callObject = $callObject;
        $call->function = $method;
        $call->arguments = new HasCalledArguments($arguments);

        /**
         * @var   null|ObjectManager   $objectManager   Менеджер мок-объекта
         * @var   null|ClassManager    $classManager    Менеджер мок-класса
         * @var   null|MethodManager   $methodManager   Менеджер мок-метода
         */
        $call->exeForMethodSearchManagers($objectManager, $classManager, $methodManager);

        if ($methodManager !== null)
        {
            return $methodManager->hasCalled($call);
        }

        return null;
    }

    /**
     * Найдет менеджеры мок-объекта, мок-класса и мок метода
     *
     * @param   null|ObjectManager   $objectManager   Менеджер мок-объекта
     * @param   null|ClassManager    $classManager    Менеджер мок-класса
     * @param   null|MethodManager   $methodManager   Менеджер мок-метода
     *
     * @return  void
     */
    private function exeForMethodSearchManagers(?ObjectManager &$objectManager, ?ClassManager &$classManager, ?MethodManager &$methodManager): void
    {
        $classManager = ClassManager::getManager($this->ownerClass);

        // * * *

        if ($this->callObject !== null)
        {
            $objectManager = ObjectManager::getManager($this->callObject);

            if ($objectManager !== null)
            {
                $methodManager = $objectManager->getMethodManager($this->function, false);
                if ($methodManager !== null) return;
            }
        }

        // * * *

        if ($classManager !== null)
        {
            $methodManager = $classManager->getMethodManager($this->function, false);
        }
    }
}
