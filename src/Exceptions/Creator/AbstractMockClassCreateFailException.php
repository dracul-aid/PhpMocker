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

use DraculAid\PhpMocker\Exceptions\PhpMockerRuntimeException;

/**
 * Абстрактный класс для исключений, связанных с созданием мок-классов
 */
abstract class AbstractMockClassCreateFailException extends PhpMockerRuntimeException {}
