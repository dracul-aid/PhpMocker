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
 *
 * Срабатывает только при создании полных мок-объектов, имеющих мок-метод конструктор.
 * Обычно срабатывает для объектов созданных на базе мок-классов созданных с помощью наследования, но не всегда,
 * в случае мок-классов созданных с помощью изменения PHP кода (так как не все мок-классы созданные с помощью изменения
 * PHP кода имеют объявленный конструктор)
 *
 * Оглавление:
 * @see ObjectManagerCreateHandler::$handlers - Список функций для срабатывания
 * @see ObjectManagerCreateHandler::exe() - Отработка события
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
     *
     * @param   ObjectManager   $objectManager   Созданный менеджер мок-объекта
     *
     * @return  void
     */
    public static function exe(ObjectManager $objectManager): void
    {
        foreach (self::$handlers as $function)
        {
            $function($objectManager);
        }
    }
}
