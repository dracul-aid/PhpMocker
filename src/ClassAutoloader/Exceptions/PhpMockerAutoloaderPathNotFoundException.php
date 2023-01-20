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
 * Используется, при провале поиска пути к файлу класса
 */
class PhpMockerAutoloaderPathNotFoundException extends PhpMockerAutoloaderPathException
{
    /**
     * @param   string   $class   Имя класса, для которого не удалось найти файл с описанием
     */
    public function __construct(string $class)
    {
        parent::__construct("Path for class {$class} not found");
    }
}
