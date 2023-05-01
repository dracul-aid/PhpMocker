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
 * Класс, для типизации свойств классов, как "функций"
 * (т.е. позволяет в свойствах класса хранить и выполнять функции)
 *
 * Оглавление:
 * @see self::$callable [const] - Вызываемая функция
 * @see self::$defaultArguments Параметры, которые будут переданы функции, при вызове с помощью call()
 * @see self::call() - Вызов прикрепленной функции, с массивом аргументов (позволяет передавать аргументы по ссылке)
 */
class CallableObject
{
    /**
     * Вызываемая функция
     */
    readonly public string|array|\Closure $callable;

    /**
     * Параметры, которые будут переданы функции, при вызове с помощью {@see CallableObject::call()}
     *
     * При вызове функции с помощью {@see CallableObject::call()} эти аргументы будут взяты, как аргументы "по умолчанию"
     */
    public array $defaultArguments = [];

    /**
     * Конструктор
     *
     * Если передан $defaultArguments, то они применяются только при использовании {@see CallableObject::call()}
     * Если функция вызвана из переменной {@see CallableObject::$callable} - то "аргументы по умолчанию" игнорируются
     *
     * @param   callable   $callable           Прикрепляемая функция
     * @param   array      $defaultArguments   Аргументы для функции "по умолчанию"
     */
    public function __construct(callable $callable, array $defaultArguments = [])
    {
        $this->callable = $callable;
        $this->defaultArguments = array_values($defaultArguments);
    }

    /**
     * Вызов объекта эквивалентен вызову функции. При вызове можно передать список аргументов.
     * Не переданные аргументы, будут дополнены из {@see self::$defaultArguments}
     *
     * @param   array   ...$arguments   Аргументы вызова функции
     *
     * @return  mixed   Вернет результат работы функции
     */
    public function __invoke(mixed ...$arguments): mixed
    {
        return $this->call($arguments);
    }

    /**
     * Вызов прикрепленной функции, с массивом аргументов (позволяет передавать аргументы по ссылке)
     * Не переданные аргументы, будут дополнены из {@see self::$defaultArguments}
     *
     * @param   array   $arguments   Аргументы вызова функции
     *
      * @return  mixed   Вернет результат работы функции
     */
    public function call(array $arguments = []): mixed
    {
        $arguments = array_replace($this->defaultArguments, $arguments);

        return ($this->callable)(...$arguments);
    }
}
