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

use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Managers\Tools\CallResult;
use DraculAid\PhpMocker\Managers\Tools\HasCalled;

/**
 * Список пользовательских функций, для выполнения в мок-методе
 *
 * Оглавление:
 * @see self::$functions - Список функций для исполнения
 * @see self::$stopMock - Нужно ли прекратить дальнейшее выполнение мок-кода
 * @see self::$resultCallResult - Объект для хранения результатов работы пользовательской функции
 */
class MethodUserFunctionsList implements MethodUserFunctionInterface
{
    /**
     * Список функций для исполнения
     *
     * @var callable[]|MethodUserFunctionsListInterface[] $functions   Массив функций (см описание в {@see MethodUserFunctionsListInterface})
     */
    public array $functions = [];

    /**
     * Вызов "Пользовательской функции" должен прекратить выполнение мок-кода или нет
     */
    public bool $stopMock;

    /**
     * Объект для хранения результатов работы пользовательской функции
     */
    public CallResult $resultCallResult;

    /**
     * @param   callable[]    $functions    Список функций (см описание в {@see MethodUserFunctionsListInterface})
     * @param   bool          $stopMock     Нужно ли прекратить дальнейшее выполнение мок-кода
     * @param   bool          $stopMethod   Нужно ли прекратить выполнение функции (т.е. вернуть мок-результат)
     * @param   null|mixed    $returnData   Что функция должна вернуть
     */
    public function __construct(array $functions, bool $stopMock, bool $stopMethod = false, mixed $returnData = null)
    {
        $this->functions = $functions;
        $this->stopMock = $stopMock;
        $this->resultCallResult = new CallResult($stopMethod, $returnData);
    }

    /**
     * @param   HasCalled       $hasCalled        Объект с параметрами вызова
     * @param   MethodManager   $methodManager    Менеджер мок-метода
     *
     * @return  null|CallResult   Любой результат кроме CallResult будет проигнорирован
     */
    public function __invoke(HasCalled $hasCalled, MethodManager $methodManager): null|CallResult
    {
        foreach ($this->functions as $function)
        {
            if (is_callable($function)) $function($hasCalled, $methodManager, $this);
        }

        return $this->stopMock ? $this->resultCallResult : null;
    }
}
