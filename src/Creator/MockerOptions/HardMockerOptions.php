<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator\MockerOptions;

use DraculAid\PhpMocker\Creator\HardMocker;

/**
 * Класс настроек создания мок-классов с помощью "изменения PHP кода", {@see HardMocker}
 *
 * Оглавление:
 * @see self::$exceptionForInterface
 */
class HardMockerOptions extends AbstractMockerOptions
{
    /**
     * Нужно ли выбросить исключение, если в PHP коде будет определение интерфейса
     */
    public bool $exceptionForInterface = true;
}
