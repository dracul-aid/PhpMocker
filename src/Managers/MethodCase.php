<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers;

use DraculAid\PhpMocker\Managers\MethodUserFunctions\MethodUserFunctionInterface;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;
use DraculAid\PhpMocker\Tools\CallableObject;

/**
 * Кейс вызова мок-метода
 *
 * Информация по счетчикам вызова:
 * Счетчики вызова по конкретным аргументам начинают срабатывать только если был установлен кейс вызова для аргументов.
 * Счетчик для вызова "по умолчанию" срабатывает всегда, если метод может быть мок-методом
 *
 * Оглавление:
 * @see MethodCase::DEFAULT - "хэш" для вызова "по умолчанию"
 * @see MethodCase::hasCalled() - Отрабатывает вызов метода для данного кейса-вызова
 * --- Свойства кейса
 * @see self::$methodManager [const] - Объект менеджер мок-метода, которому принадлежит кейс вызова
 * @see self::$index [readonly] - Хэш от аргументов вызова
 * @see self::$arguments [readonly] - Аргументы вызова кейса
 * @see self::isWork() - Вернет указание, что кейс вызова может быть обработан
 * --- Счетчики вызова кейса
 * @see self::$countCall - Счетчик срабатываний кейса (может быть сброшен)
 * @see self::$countAllCall - Счетчик срабатываний кейса (не может быть сброшен)
 * --- Пользовательская функция, предваряющая выполнение кейса
 * @see self::$userFunction - Функция, которая будет выполнена перед выполнением основного тела метода
 * @see self::setUserFunction() - Установит функцию, которая будет выполнена перед выполнением кейса-вызова
 * --- Возвращаемое кейсом значение
 * @see self::$isCanReturn - Указание, что кейс вызова должен вернуть результат
 * @see self::$canReturnData - Какие данные должен вернуть мок-метод
 * @see self::setWillReturn() - Установит ответ для функции, для данного кейса
 * --- Выброс исключения при вызове метода
 * @see self::$canReturnException - Исключение, которое должно быть выброшено при обращении к мок-методу
 * @see self::setWillException() - Установит объект-исключение, которое должно будет быть выброшено при вызове метода
 * --- Перезапись значений аргументов метода
 * @see self::$rewriteArguments - Массив, с значениями для перезаписи аргументов метода
 * @see self::setRewriteArguments() - Установит значения для перезаписи аргументов метода
 * --- Прочее
 * @see self::setWillReturnClear() - Отменит установленный ответ для функции (т.е. функция для указанных аргументов будет работать стандартно)
 *
 * Свойства, доступные для чтения через @see self::__get()
 * @property $countCall
 * @property $countAllCall
 * @property $isCanReturn
 * @property $canReturnData
 */
class MethodCase
{
    public const DEFAULT = '__default__';

    /**
     * Объект менеджер мок-метода, которому принадлежит кейс вызова
     */
    readonly public MethodManager $methodManager;

    /**
     * Хэш от аргументов вызова, или значение константы @see MethodCase::DEFAULT для вызовов "по умолчанию"
     * @see MethodCase::$arguments - аргументы вызова
     */
    readonly public string $index;

    /**
     * Аргументы вызова кейса
     * @see MethodCase::$index - хэш от аргументов вызова
     *
     * @see MethodManager::caseIndex() - Построение хэша от аргументов вызова
     *
     */
    readonly public array $arguments;

    /**
     * Исключение, которое должно быть выброшено при обращении к мок-методу
     *
     * NULL - исключение не будет выброшено.
     */
    public null|\Throwable $canReturnException = null;

    /**
     * Функция, которая будет выполнена перед выполнением основного тела метода
     * (только, если метод может быть мок-методом)
     *
     * @see \DraculAid\PhpMocker\Managers\MethodUserFunctions\MethodUserFunctionInterface::__invoke() Описание входящих параметров функции и ее ответа
     */
    public null|CallableObject|MethodUserFunctionInterface $userFunction = null;

    /**
     * Счетчик срабатываний кейса (может быть сброшен)
     */
    private int $countCall = 0;

    /**
     * Счетчик срабатываний кейса (не может быть сброшен)
     */
    private int $countAllCall = 0;

    /**
     * Массив, с значениями для перезаписи аргументов метода
     *    Ключ: имя аргумента
     *    Значение: значение, для записи в аргумент
     */
    public array $rewriteArguments = [];

    /**
     * Указание, что кейс вызова должен вернуть результат
     * (т.е. основное тело функции не будет выполнено)
     *
     * Возвращаемый результат @see MethodCase::$canReturnData
     */
    private bool $isCanReturn = false;

    /**
     * Какие данные должен вернуть мок-метод
     *
     * Данные будут возвращены, если только @see MethodCase::$isCanReturn === true
     */
    private mixed $canReturnData = null;

    /**
     * @param   MethodManager   $method      Менеджер мок-метода
     * @param   string          $index       Хэш аргументов вызова
     * @param   array           $arguments   Массив аргументов вызова метода
     */
    public function __construct(MethodManager $method, string $index, array $arguments)
    {
        $this->methodManager = $method;
        $this->index = $index;
        $this->arguments = $arguments;
    }

    public function __get(string $name): mixed
    {
        return $this->{$name};
    }

    /**
     * Вернет указание, что кейс вызова может быть обработан
     * (он вернет ответ функции, выбросит исключение, или для него установлена пользовательская функция)
     *
     * @return bool
     */
    public function isWork(): bool
    {
        return $this->isCanReturn || count($this->rewriteArguments) > 0 || $this->canReturnException !== null || $this->userFunction !== null;
    }

    /**
     * Установит ответ для функции, для данного кейса
     *
     * @param   mixed   $data           Результат работы функции
     * @param   bool    $clearCounter   TRUE, если нужно сбросить счетчик вызова кейса
     *
     * @return  $this
     */
    public function setWillReturn(mixed $data = null, bool $clearCounter = false): self
    {
        $this->isCanReturn = true;
        $this->canReturnData = $data;

        if ($clearCounter) $this->countCall = 0;

        return $this;
    }

    /**
     * Установит значения для перезаписи аргументов
     *
     * @param   array   $arguments      Массив аргументов для замены (ключ - имя аргумента)
     * @param   bool    $clearCounter   TRUE, если нужно сбросить счетчик вызова кейса
     *
     * @return $this
     */
    public function setRewriteArguments(array $arguments, bool $clearCounter = false): self
    {
        $this->rewriteArguments = $arguments;

        if ($clearCounter) $this->countCall = 0;

        return $this;
    }

    /**
     * Установит объект-исключение, которое должно будет быть выброшено при вызове метода
     *
     * @param   null|\Throwable   $exceptionObject         Объект-исключение
     * @param   bool              $clearCounter            TRUE, если нужно сбросить счетчик вызова кейса
     * @param   bool              $clearFunctionAndReturn  TRUE, если нужно удалить ранее установленный ответ функции и пользовательскую функцию
     *
     * @return  $this
     */
    public function setWillException(null|\Throwable $exceptionObject, bool $clearCounter = false, bool $clearFunctionAndReturn = true): self
    {
        $this->canReturnException = $exceptionObject;

        if ($clearCounter) $this->countCall = 0;

        if ($clearFunctionAndReturn)
        {
            $this->isCanReturn = false;
            $this->canReturnData = null;
            $this->userFunction = null;
        }

        return $this;
    }

    /**
     * Установит функцию, которая будет выполнена перед выполнением кейса-вызова
     *
     * Аргументы и описание работы функции {@see MethodUserFunctionInterface}
     *
     * @param   null|callable|CallableObject|MethodUserFunctionInterface   $userFunction    Может быть любой callable, или объект-функция. NULL - для удаления функции
     * @param   bool                                                       $clearCounter    TRUE, если нужно сбросить счетчик вызова кейса
     *
     * @return  $this
     */
    public function setUserFunction(null|callable|CallableObject|MethodUserFunctionInterface $userFunction, bool $clearCounter = false): self
    {
        if ($userFunction === null || is_a($userFunction, CallableObject::class) || is_a($userFunction, MethodUserFunctionInterface::class))
        {
            $this->userFunction = $userFunction;
        }
        else
        {
            $this->userFunction = new CallableObject($userFunction);
        }

        // * * *

        if ($clearCounter) $this->countCall = 0;

        return $this;
    }

    /**
     * Отменит установленный ответ для функции, указание на выбрасывание исключения и выполнение пользовательской функции
     * (т.е. функция для указанных аргументов будет работать стандартно)
     *
     * @param   bool    $clearCounter   TRUE, если нужно сбросить счетчик вызова кейса
     *
     * @return  $this
     */
    public function setWillReturnClear(bool $clearCounter = false): self
    {
        $this->isCanReturn = false;
        $this->canReturnData = null;
        $this->canReturnException = null;
        $this->userFunction = null;

        if ($clearCounter) $this->countCall = 0;

        return $this;
    }

    /**
     * Отрабатывает вызов метода для данного кейса-вызова
     *
     * В результате работы может быть выброшено исключение хранимое в @see MethodCase::$canReturnException
     *
     * @return   CallResult   Вернет объект "результат вызова мок-метода"
     *
     * @throws  \Throwable
     */
    public function hasCalled(HasCalled $calledData): CallResult
    {
        $this->countCall++;
        $this->countAllCall++;

        if ($this->userFunction !== null)
        {
            $resultUserFunction = $this->userFunction->__invoke($calledData, $this->methodManager);
            if (is_a($resultUserFunction, CallResult::class))
            {
                return $resultUserFunction;
            }
        }

        if ($this->canReturnException !== null)
        {
            throw $this->canReturnException;
        }

        return new CallResult($this->isCanReturn, $this->canReturnData, $this->rewriteArguments);
    }
}
