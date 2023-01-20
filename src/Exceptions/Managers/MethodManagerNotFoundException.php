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

/**
 * Исключение, при попытке получить "менеджер мок-метода" для метода, которого нет в мок-классе или мок-объекте
 */
class MethodManagerNotFoundException extends AbstractExceptionMethodManagerException
{
    /**
     * @param   string   $methodName   Имя метода
     * @param   string   $addDesc      Текст дополнительного описания ошибки
     */
    public function __construct(string $methodName, string $addDesc = '')
    {
        parent::__construct(
            "Method {$methodName} not found"
            . ($addDesc ? "; {$addDesc}" : '')
        );
    }
}
