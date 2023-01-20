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

/**
 * Исключение, для случаев попытки создать "Мок класс с помощью изменения PHP кода" для интерфейсов
 */
class HardMockerCreateForInterface extends AbstractMockClassCreateFailException
{
    /**
     * @param   string   $interfaceName   Имя интерфейса
     */
    public function __construct(string $interfaceName)
    {
        parent::__construct("Class {$interfaceName} is a Interface");
    }
}
