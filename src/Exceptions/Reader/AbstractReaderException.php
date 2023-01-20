<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Exceptions\Reader;

use DraculAid\PhpMocker\Exceptions\Creator\AbstractMockClassCreateFailException;

/**
 * Абстрактный класс для проблем при создании схем классов (чтения PHP кода или чтении из рефлексии)
 */
abstract class AbstractReaderException extends AbstractMockClassCreateFailException {}
