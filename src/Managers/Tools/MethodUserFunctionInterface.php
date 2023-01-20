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

use DraculAid\PhpMocker\Managers\MethodCase;
use DraculAid\PhpMocker\Managers\MethodManager;

/**
 * Интерфейс, описывающий объекты-функции, выполняющиеся перед началом выполнения мок-метода или мок-кейса
 *
 * Последовательность событий при вызове мок-метода:
 * 1) Отрабатывается объект-функция из "менеджера мок-метода" @see MethodManager::$userFunction
 * 2) Отрабатывается объект-функция из "кейса вызова" @see MethodCase::$userFunction
 * 3) Если в "кейсе вызова" есть результат работы функции (в том числе и исключение) - будет использовано оно
 * 4) Код самого метода
 *
 * Основное предназначение, использование в:
 * @see MethodManager::$userFunction Пользовательская функция для мок-метода (всех вызовов)
 * @see MethodCase::$userFunction Пользовательская функция для конкретного кейса вызова
 * @see MethodManager::hasCalled() Обработка вызова мок-метода
 */
interface MethodUserFunctionInterface
{
    /**
     * @param   HasCalled       $hasCalled        Объект с параметрами вызова
     * @param   MethodManager   $methodManager    Менеджер мок-метода
     *
     * @return  CallResult|mixed   Любой результат кроме CallResult будет проигнорирован
     *
     * Если функция вернет объект @see CallResult То это приостановит выполнение мок-метода, поиск по кейсам-вызова
     * не будет проводиться, точно также, как и не будет отработан код самого метода
     */
    public function __invoke(HasCalled $hasCalled, MethodManager $methodManager): mixed;
}
