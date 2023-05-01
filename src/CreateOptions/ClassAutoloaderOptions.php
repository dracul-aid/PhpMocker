<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\CreateOptions;

use DraculAid\PhpMocker\ClassAutoloader\Autoloader;
use DraculAid\PhpMocker\Creator\MockerOptions\AbstractMockerOptions;
use DraculAid\PhpMocker\Creator\MockerOptions\HardMockerOptions;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Настройка создания мок-классов для автозагрузчика классов {@see Autoloader}
 */
class ClassAutoloaderOptions implements CreateOptionsInterface
{
    /**
     * Вызывается перед началом создания мок-класса в автозагрузчике классов
     *
     * @param   ClassScheme                                 $classesScheme    Схема создаваемого класса
     * @param   AbstractMockerOptions|HardMockerOptions     $mockerOptions    Опции создания мок-классов
     * @return void
     */
    public function __invoke(ClassScheme $classesScheme, AbstractMockerOptions $mockerOptions): void
    {
        $mockerOptions->exceptionForInterface = false;
    }
}
