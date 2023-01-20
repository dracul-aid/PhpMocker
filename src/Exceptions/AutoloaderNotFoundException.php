<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Exceptions;

/**
 * Исключение используемое, в случае, если не был найден автозагрузчки классов PhpMocker-а
 */
class AutoloaderNotFoundException extends PhpMockerRuntimeException
{
    public function __construct(string $class)
    {
        parent::__construct("Class {$class} have not loaded, because PhpMocker Autoloader not found in list of autoloader classes PHP");
    }
}
