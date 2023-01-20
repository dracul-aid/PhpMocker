<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers\Events;

use DraculAid\PhpMocker\Managers\ObjectManager;
use DraculAid\PhpMocker\Tools\CallableObject;

/**
 * Используется для работы с событиями "создание менеджера мок-объекта" @see ObjectManager
 * (Установленные функции будут срабатывать при создании каждого менеджера объектов)
 */
class ObjectManagerCreateHandler
{
    /**
     * Список функций срабатывающих при создании мок-объектов
     *
     * @var callable[]|CallableObject[] $handlers
     */
    public static array $handlers = [];

    /**
     * Срабатывает, при создании мок-объекта
     */
    public static function exe(ObjectManager $objectManager): void
    {
        foreach (self::$handlers as $function)
        {
            $function($objectManager);
        }
    }
}
