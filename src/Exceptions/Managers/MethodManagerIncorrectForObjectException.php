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
 * Исключение, при попытке получить "менеджер мок-метода" для метода, который не подходит для использования с "менеджером мок-объектов"
 */
class MethodManagerIncorrectForObjectException extends AbstractExceptionMethodManagerException
{
    /**
     * @param   \ReflectionMethod   $reflectionMethod   Рефлексия метода
     */
    public function __construct(\ReflectionMethod $reflectionMethod)
    {
        $errorMessage = [];
        if ($reflectionMethod->isStatic()) $errorMessage[] = "static";
        if ($reflectionMethod->isAbstract()) $errorMessage[] = "abstract";

        parent::__construct(
            "Method {$reflectionMethod->getName()} is a " . implode(' and ', $errorMessage) . ' method'
        );
    }
}
