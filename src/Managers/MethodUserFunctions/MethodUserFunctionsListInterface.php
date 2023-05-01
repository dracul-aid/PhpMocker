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
 * Интерфейс, для описания аргументов функций объекта "список-функций" для изменения поведения мок-метода {@see MethodUserFunctionsList}
 */
interface MethodUserFunctionsListInterface extends MethodUserFunctionInterface
{
    /**
     * Если функция вернет объект {@see CallResult} То это приостановит выполнение мок-метода, поиск по кейсам-вызова
     * не будет проводиться, точно также, как и не будет отработан код самого метода
     *
     * @param   HasCalled                       $hasCalled        Объект с параметрами вызова
     * @param   MethodManager                   $methodManager    Менеджер мок-метода
     * @param   null|MethodUserFunctionsList    $functionsList    Объект "список-функций" для изменения поведения мок-метода
     *
     * @return  CallResult|mixed   Любой результат кроме CallResult будет проигнорирован
     */
    public function __invoke(HasCalled $hasCalled, MethodManager $methodManager, null|MethodUserFunctionsList $functionsList = null): mixed;
}
