<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Exceptions\Managers;

use DraculAid\PhpMocker\Exceptions\PhpMockerLogicException;

/**
 * Исключение, выбрасывается, при попытках работать с методом, как с методом-конструктором, если метод не является конструктором
 */
class MethodIsNotConstructorException extends PhpMockerLogicException
{
    /**
     * @param   string   $methodName   Имя метода
     * @param   string   $addDesc      Текст дополнительного описания ошибки
     */
    public function __construct(string $methodName, string $addDesc = '')
    {
        parent::__construct(
            "Method {$methodName} is not a constructor"
            . ($addDesc ? "; {$addDesc}" : '')
        );
    }
}
