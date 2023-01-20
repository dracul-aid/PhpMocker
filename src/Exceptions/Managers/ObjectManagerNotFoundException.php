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

use DraculAid\PhpMocker\Exceptions\PhpMockerRuntimeException;

/**
 * Исключение для случаев, если не был найден "менеджер мок-объекта"
 *
 * Оглавление:
 * @see ObjectManagerNotFoundException::$toObject - Объект, для которого не удалось найти менеджер
 */
class ObjectManagerNotFoundException extends PhpMockerRuntimeException implements ManagerNotFoundExceptionInterface
{
    /**
     * Объект, для которого не удалось найти менеджер
     */
    readonly public object $toObject;

    /**
     * @param  object  $object  Объект, для которого не удалось найти менеджер
     */
    public function __construct(object $object)
    {
        $this->toObject = $object;

        parent::__construct("Not found manager for object");
    }
}
