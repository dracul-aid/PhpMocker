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

use DraculAid\PhpMocker\Creator\AbstractMocker;
use DraculAid\PhpMocker\Creator\MockerOptions\AbstractMockerOptions;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Интерфейс с описанием классов, используемых для установки параметров создания мок-классов
 *
 * Для установки параметров создания может быть передана любая функция (в том числе и объект с методом __invoke())
 * Функция будет вызвана перед началом создания мок-класса {@see AbstractMocker::createClassExecuting()}
 *
 * В качестве параметров функция получает:
 * 1. Схему создаваемого класса {@see ClassScheme}
 * 2. Объект с настройками создания мок-класса {@see AbstractMockerOptions}
 */
interface CreateOptionsInterface
{
    /**
     * Вызывается перед началом создания мок-класса
     *
     * @param   ClassScheme             $classesScheme   Схему создаваемого класса
     * @param   AbstractMockerOptions   $mockerOptions   Объект с настройками создания мок-класса
     *
     * @return void
     */
    public function __invoke(ClassScheme $classesScheme, AbstractMockerOptions $mockerOptions): void;
}
