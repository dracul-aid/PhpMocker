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
 * Исключение, используется, если мок-класс с помощью наследования не был создан, так как класс-источник для мока - финальный класс
 */
class SoftMockClassCreatorClassIsFinalException extends AbstractMockClassCreateFailException
{
    /**
     * @param   string   $className   Имя класса
     */
    public function __construct(string $className)
    {
        parent::__construct("Class {$className} is a final class");
    }
}
