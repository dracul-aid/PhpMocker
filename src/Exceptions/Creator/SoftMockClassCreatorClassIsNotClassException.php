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
 * Исключение, для случаев, когда не удалось создать мок-класс с помощью наследования, так как это попытка создать
 * мок для НЕ поддерживаемого типа класса
 */
class SoftMockClassCreatorClassIsNotClassException extends AbstractMockClassCreateFailException
{
    /**
     * @param string $classType Тип класса (класс, интерфейс...)
     * @param string $className Имя класса
     */
    public function __construct(string $classType, string $className)
    {
        parent::__construct("Class {$className} is not a class or abstract class. It is a {$classType}");
    }
}
