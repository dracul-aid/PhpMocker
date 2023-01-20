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
 * Используется в случаях, если нет прав на чтение файла класса
 */
class PhpMockerAutoloaderPathIsNotReadableException extends PhpMockerAutoloaderPathException
{
    /**
     * @param   string   $path    Путь к файлу класса
     */
    public function __construct(string $path)
    {
        parent::__construct("Path {$path} is not a readable file");
    }
}
