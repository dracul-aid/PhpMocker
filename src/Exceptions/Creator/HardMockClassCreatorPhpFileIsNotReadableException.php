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
 * Исключение, для случаев, если не удалось создать мок-класс с помощью изменения PHP кода, так как файл с описанием
 * класса не был найден
 */
class HardMockClassCreatorPhpFileIsNotReadableException extends AbstractHardMockClassCreatorPhpException
{
    protected static function createMessage(string $filePath): string
    {
        return "Path {$filePath} is not readable";
    }
}
