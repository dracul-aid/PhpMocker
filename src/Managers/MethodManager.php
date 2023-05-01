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

use DraculAid\PhpMocker\Creator\AbstractMocker;
use DraculAid\PhpMocker\Exceptions\PhpMockerLogicException;
use DraculAid\PhpMocker\Managers\MethodUserFunctions\MethodUserFunctionInterface;
use DraculAid\PhpMocker\Managers\Tools\AbstractClassAndObjectManager;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;
use DraculAid\PhpMocker\Managers\Tools\HasCalledArguments;
use DraculAid\PhpMocker\Tools\CallableObject;

/**
 * Менеджер мок-метода
 *
 * Оглавление:
 * @see self::$ownerManager [const] - Объект-менеджер - владелец менеджера метода
 * @see self::$name [readonly] - Имя метода
 * @see self::call() - Вызов метода (в том числе и protected и private)
 * @see self::clearCases() Сбрасывает все "кейсы вызова" и счетчики вызовов
 * --- Счетчики вызовов мок-метода
 * @see self::$countCall - Общий счетчик числа вызовов метода
 * @see self::$countCallWithoutCases - Счетчик вызовов без отработанных кейсов вызова
 * @see self::$countCallUserFunctionReturn - Счетчик вызовов "Функции, которая будет выполнена перед выполнением основного тела метода" с перехватом выполнения основного кода
 * --- Кейсы вызова
 * @see self::$cases - Массив со списком всех кейсов вызовов
 * @see self::$userFunction - Функция, которая будет выполнена перед выполнением основного тела метода (только, если метод может быть мок-методом)
 * @see self::setUserFunction() - Установит пользовательскую функцию, предваряющую выполнение мок-метода
 * @see self::case() - Вернет кейс вызова метода (если нет, создаст его)
 * @see self::defaultCase() - Вернет кейс вызова метода "по умолчанию"
 * --- Прочее
 * @see self::hasCalled() - Отрабатывает вызов метода для данного кейса-вызова
 */
class MethodManager
{
    /**
     * Объект-менеджер - владелец менеджера метода
     */
    readonly public AbstractClassAndObjectManager $ownerManager;

    /**
     * Имя метода
     */
    readonly public string $name;

    /**
     * Массив со списком кейсов вызовов для мок-метода
     *
     * * Ключи [string]: хэш от аргументов вызова, {@see MethodCase::$index}
     * * Значения: объект кейс-вызова
     *
     * @see MethodManager::caseIndex() - Формирование хэша от аргументов вызова
     *
     * @var MethodCase[] $cases
     */
    public array $cases = [];

    /**
     * Общий счетчик числа вызовов метода
     */
    public int $countCall = 0;

    /**
     * Счетчик вызовов без отработанных кейсов вызова
     */
    public int $countCallWithoutCases = 0;

    /**
     * Счетчик вызовов "Функции, которая будет выполнена перед выполнением основного тела метода" с перехватом выполнения основного кода
     * @see self::$userFunction - хранит функцию
     */
    public int $countCallUserFunctionReturn = 0;

    /**
     * Функция, которая будет выполнена перед выполнением основного тела метода
     * (только, если метод может быть мок-методом)
     *
     * @see MethodUserFunctionInterface::__invoke() Описание входящих параметров функции и ее ответа
     * @see self::countCallUserFunctionReturn Хрнит счетчик вызова функции, с перехватом выполнения основного кода
     */
    public null|CallableObject|MethodUserFunctionInterface $userFunction = null;

    /**
     * @param   AbstractClassAndObjectManager   $owner   Объект "владелец метода" (менеджер мок-класса или мок-объекта)
     * @param   string                          $name    Имя метода
     */
    public function __construct(AbstractClassAndObjectManager $owner, string $name)
    {
        $this->ownerManager = $owner;
        $this->name = $name;
    }

    /**
     * Вызов метода (в том числе и protected и private)
     *
     * @param   array   $arguments    Аргументы вызываемого метода
     *
     * @return  mixed    Вернет результат работы функции
     */
    public function call(array $arguments = []): mixed
    {
        return $this->ownerManager->callMethod($this->name, $arguments);
    }

    /**
     * Установит пользовательскую функцию, предваряющую выполнение мок-метода
     *
     * @param   null|callable|CallableObject|MethodUserFunctionInterface   $userFunction   Может быть любой callable, или объект-функция. NULL - для удаления функции
     *
     * @return  $this
     */
    public function setUserFunction(null|callable|CallableObject|MethodUserFunctionInterface $userFunction): self
    {
        if ($userFunction === null || is_a($userFunction, CallableObject::class) || is_a($userFunction, MethodUserFunctionInterface::class))
        {
            $this->userFunction = $userFunction;
        }
        else
        {
            $this->userFunction = new CallableObject($userFunction);
        }

        return $this;
    }

    /**
     * Вернет кейс вызова для указанных аргументов (если такого кейса нет - создаст его)
     *
     * @param   mixed   ...$arguments   Аргументы вызова
     *
     * @return  MethodCase   Вернет объект кейс вызова
     */
    public function case(mixed ... $arguments): MethodCase
    {
        return $this->getOrCreateCase($this->caseIndex($arguments), $arguments);
    }

    /**
     * Вернет кейс вызова метода "по умолчанию"
     *
     * @return   MethodCase   Вернет объект кейс вызова
     *
     * @throws  \LogicException  Если метод не может использоваться как "мок-метод"
     */
    public function defaultCase(): MethodCase
    {
        return $this->getOrCreateCase(MethodCase::DEFAULT, []);
    }

    /**
     * Сбрасывает все "кейсы вызова" и счетчики вызовов.
     *
     * @return $this
     */
    public function clearCases(): static
    {
        foreach ($this->cases as $caseIndex => $caseData)
        {
            unset($this->cases[$caseIndex]);
        }

        $this->userFunction = null;

        $this->countCall = 0;
        $this->countCallUserFunctionReturn = 0;
        $this->countCallWithoutCases = 0;

        return $this;
    }

    /**
     * Отрабатывает вызов метода для данного кейса-вызова
     *
     * В результате работы может быть выброшено исключение хранимое в @see MethodCase::$canReturnException
     *
     * @param   HasCalled   $calledData   Объект с параметрами вызова метода
     *
     * @return   CallResult   Вернет объект "результат вызова мок-метода"
     *
     * @throws  \Throwable
     */
    public function hasCalled(HasCalled $calledData): CallResult
    {
        $this->countCall++;

        // * * *

        if ($this->userFunction != null)
        {
            /** @see MethodUserFunctionInterface Формат вызова функции */
            $userFunctionReturn = $this->userFunction->call([$calledData, $this]);
            if (is_a($userFunctionReturn, CallResult::class))
            {
                $this->countCallUserFunctionReturn++;
                return $userFunctionReturn;
            }
        }

        // * * *

        $calledDataIndex = $this->caseIndex($calledData->arguments);

        if (isset($this->cases[$calledDataIndex]) && $this->cases[$calledDataIndex]->isWork())
        {
            return $this->cases[$calledDataIndex]->hasCalled($calledData);
        }
        elseif ($this->defaultCase()->isWork())
        {
            return $this->defaultCase()->hasCalled($calledData);
        }
        else
        {
            $this->countCallWithoutCases++;
            return CallResult::none();
        }
    }

    /**
     * Вернет существующий кейс вызова или, кейса с таким индексом нет, создаст новый кейс вызова
     *
     * @param   string   $caseIndex   Индекс кейса (хэш от аргументов вызова)
     * @param   array    $arguments   Аргументы вызова
     *
     * @return  MethodCase   Вернет объект "кейс вызова"
     *
     * @throws  PhpMockerLogicException  Если метод не может использоваться как "мок-метод"
     */
    public function getOrCreateCase(string $caseIndex, array $arguments): MethodCase
    {
        if (empty($this->cases[$caseIndex]))
        {
            if (!$this->ownerManager->methodIsMock($this->name))
            {
                /** @see AbstractMocker::getTextWhyMethodIsNotMockMethod() */
                $errDesc = $this->ownerManager->getDriver()::getTextWhyMethodIsNotMockMethod($this->ownerManager->getTo(), $this->name);
                throw new PhpMockerLogicException($errDesc);
            }

            $this->cases[$caseIndex] = new MethodCase($this, $caseIndex, $arguments);
        }

        return $this->cases[$caseIndex];
    }

    /**
     * Вернет хэш для аргументов вызова метода
     *
     * @param   array|HasCalledArguments   $arguments   Аргументы вызова метода
     *
     * @return  string   Хэш от аргументов
     */
    private function caseIndex(array|HasCalledArguments $arguments): string
    {
        if (is_object($arguments)) $arguments = iterator_to_array($arguments->for(false));

        // Тесты показали, что json_encode() быстрее serialize()
        return hash('crc32c', json_encode(array_values($arguments)));
    }
}
