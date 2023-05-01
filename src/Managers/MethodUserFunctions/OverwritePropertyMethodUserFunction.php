<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers\MethodUserFunctions;

use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodCase;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;

/**
 * Класс-функция "пользовательская функция" для мок-методов, используется для перезаписи свойств объекта (или класса)
 *
 * (!) При вызове в статическом методе, устанавливает статические свойства класса, вне статического контекста - свойства объекта
 * (!) Используется для сброса стандартных конструкторов классов, используется в {@see MethodCase::setClearConstructor()}
 *     и переназначения аргументов объекта (в том числе и не публичных)
 */
class OverwritePropertyMethodUserFunction implements MethodUserFunctionInterface
{
    /**
     * Массив, для перезаписи свойств объекта
     *
     * @var array<string, mixed> $setPropertyData (ключи - имена свойств)
     */
    public array $setPropertyData;

    /**
     * Нужно ли прекратить дальнейшее выполнение мок-кода
     */
    public bool $stopMock;

    /**
     * Нужно ли прекратить выполнение функции (т.е. вернуть мок-результат)
     * (Если нужна, также следует установить {@see self::$returnData})
     */
    public bool $stopMethod;

    /**
     * Установленный для ответа функции мок-результат (Если {@see self::$stopMethod} === true)
     */
    public mixed $returnData;

    /**
     * @param   array<string, mixed>   $setPropertyData   Массив, для перезаписи свойств объекта (ключи - имена свойств)
     * @param   bool                   $stopMock          Нужно ли прекратить дальнейшее выполнение мок-кода
     * @param   bool                   $stopMethod        Нужно ли прекратить выполнение функции (т.е. вернуть мок-результат)
     * @param   mixed                  $returnData        Установленный для ответа функции мок-результат (если установлен $stopMethod)
     */
    public function __construct(array $setPropertyData, bool $stopMock, bool $stopMethod = false, mixed $returnData = null)
    {
        $this->setPropertyData = $setPropertyData;
        $this->stopMock = $stopMock;
        $this->stopMethod = $stopMethod;
        $this->returnData = $returnData;
    }

    /**
     * @param   HasCalled       $hasCalled        Объект с параметрами вызова
     * @param   MethodManager   $methodManager    Менеджер мок-метода
     *
     * @return  null|CallResult   Функция вернет указание, должен ли метод выполняться дальше или нет
     */
    public function __invoke(HasCalled $hasCalled, MethodManager $methodManager): null|CallResult
    {
        if ($hasCalled->callObject === null)
        {
            ClassManager::getManager($hasCalled->callClass)->setProperty($this->setPropertyData);
        }
        else
        {
            ObjectManager::getManager($hasCalled->callObject)->setProperty($this->setPropertyData);
        }

        // * * *

        if ($this->stopMethod)
        {
            return $this->stopMock ? CallResult::createForStopMethod($this->returnData) : null;
        }
        else
        {
            return $this->stopMock ? new CallResult(false) : null;
        }
    }
}
