<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Exceptions\Creator;

/**
 * Исключение, используется, если мок-класс с помощью наследования не был создан, так как не удалось получить рефлексию класса
 */
class SoftMockClassClassNotFoundException extends AbstractMockClassCreateFailException
{
    /**
     * @param   string   $className           Имя класса
     * @param   string   $reflectionMessage   Ошибка полученная
     */
    public function __construct(string $className, string $reflectionMessage)
    {
        parent::__construct("Class {$className} not found; ReflectionException: {$reflectionMessage}");
    }
}
