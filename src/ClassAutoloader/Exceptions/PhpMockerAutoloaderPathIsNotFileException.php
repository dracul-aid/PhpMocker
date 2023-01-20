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
 * Используется в случаях, если путь к файлу класса ведет не на файл
 */
class PhpMockerAutoloaderPathIsNotFileException extends PhpMockerAutoloaderPathException
{
    /**
     * @param   string   $path    Путь к файлу класса
     */
    public function __construct(string $path)
    {
        if (file_exists($path)) parent::__construct("Path {$path} is not a file path, it is a " . filetype($path));
        else parent::__construct("Path {$path} is not exists");
    }
}
