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
 * Исключение, для случаев, когда мок-класс не был создан, так как имя для него уже занято
 * (класс с таким именем уже был загружен)
 */
class MockClassCreatorClassWasLoadedException extends AbstractMockClassCreateFailException
{
    /**
     * @param   string   $classType   Тип класса (класс, интерфейс...)
     * @param   string   $className   Имя класса
     */
    public function __construct(string $classType, string $className)
    {
        parent::__construct(ucfirst($classType) . " {$className} was loaded");
    }
}
