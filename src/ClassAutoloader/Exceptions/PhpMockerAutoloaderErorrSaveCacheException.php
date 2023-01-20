<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\ClassAutoloader\Exceptions;

/**
 * Исключение вызываемое ошибками записи кода мок-класса в кеш
 */
class PhpMockerAutoloaderErorrSaveCacheException extends \RuntimeException implements PhpMockerAutoloaderExceptionInterface
{
    /**
     * @param   string   $path      Путь для сохранения кеша, который вызвал проблемы
     * @param   string   $message   Текст с дополнительным описанием проблемы
     */
    public function __construct(string $path, string $message = '')
    {
        parent::__construct(
            "PHP code for mock-class did no save in cache: {$path}"
            . ($message === '' ? '' : ". {$message}")
        );
    }
}
