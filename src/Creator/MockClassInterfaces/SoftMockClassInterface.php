<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator\MockClassInterfaces;

/**
 * Интерфейс, для указания, что класс является "мок-классом" созданным с помощью наследования
 * @see \DraculAid\PhpMocker\Creator\SoftMocker
 */
interface SoftMockClassInterface extends MockClassInterface {}
