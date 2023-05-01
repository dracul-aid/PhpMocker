<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator\Events;

use DraculAid\PhpMocker\Creator\AbstractMocker;
use DraculAid\PhpMocker\Exceptions\Creator\BeforeCreateMockClassStopException;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Tools\CallableObject;

/**
 * Используется для работы с событиями "Перед созданием мок-класса" {@see AbstractMocker::run()}
 * (Установленные функции будут срабатывать перед созданием каждого мок-класса)
 *
 * Позволяет остановить создание мок-класса
 *
 * Оглавление:
 * @see BeforeCreateMockClassHandler::$handlers - Список функций для срабатывания
 * @see BeforeCreateMockClassHandler::exe() - Отработка события
 */
class BeforeCreateMockClassHandler
{
    /**
     * Список функций срабатывающих при создании мок-объектов
     *
     * В момент возникновения события, функция получит следующие аргументы:
     * 1. {@see ClassScheme}  Схема создаваемого класса
     * 2. string              PHP код создаваемого класса. Будет передано по ссылке, код можно изменять.
     * 3. string              Имя класса с помощью которого создается мок-класс
     *
     * Если функция вернет TRUE - создание мок-класса не будет проведено (!)
     * Любой другой ответ функции будет проигнорирован
     *
     * Первая функция, которая вернет TRUE, остановит создание мок-класса
     * В случае остановки, будет выброшено исключение @see
     *
     * @var callable[]|CallableObject[] $handlers
     */
    public static array $handlers = [];

    /**
     * Срабатывает, при создании мок-объекта
     *
     * @param   ClassScheme   $classScheme   Схема создаваемого класса
     * @param   string       &$phpCode       PHP код создаваемого класса
     * @param   string        $driver        Имя класса с помощью которого создается мок-класс
     *
     * @return  void
     *
     * @throws  BeforeCreateMockClassStopException   Если одна из установленных функций события остановила создание мок-класса
     */
    public static function exe(ClassScheme $classScheme, string &$phpCode, string $driver): void
    {
        foreach (self::$handlers as $handlerIndex => $function)
        {
            if ($function($classScheme, $phpCode, $driver) === true)
            {
                throw new BeforeCreateMockClassStopException($classScheme->getFullName(), $handlerIndex);
            }
        }
    }
}
