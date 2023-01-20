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
 * Абстрактный класс для создания мок-классов с помощью изменения PHP кода, для случаев, когда код должен был браться
 * из файла, но есть проблемы с доступа к файлу
 */
abstract class AbstractHardMockClassCreatorPhpException extends AbstractMockClassCreateFailException
{
    /**
     * @param   string   $filePath   Путь до проблемного файла
     */
    public function __construct(string $filePath)
    {
        parent::__construct(static::createMessage($filePath));
    }

    /**
     * Создает сообщение для исключения
     *
     * @param   string   $filePath   Путь до проблемного файла
     *
     * @return  string
     */
    abstract protected static function createMessage(string $filePath): string;
}
