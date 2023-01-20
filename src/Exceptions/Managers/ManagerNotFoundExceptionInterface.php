<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Exceptions\Managers;

use DraculAid\PhpMocker\Exceptions\PphMockerExceptionInterface;

/**
 * Интерфейс для исключений связанных с невозможность вернуть "менеджер моков"
 */
interface ManagerNotFoundExceptionInterface extends PphMockerExceptionInterface {}
