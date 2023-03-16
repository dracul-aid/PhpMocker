<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\ClassAutoloader\Filters;

/**
 * Интерфейс для проверки классов, должны ли они быть преобразованны в мок-классы или нет
 *
 * Оглавление:
 * @see AutoloaderFilterInterface::PHP_MOCKER_NAMESPACE - Пространство имен PhpMocker-а
 * @see AutoloaderFilterInterface::PHP_UNIT_NAMESPACES - Список пространств имен PhpUnit библиотеки, и связанных с нею пакетов, которые не должны преобразовываться в мок-классы
 * @see self::canBeMock() - Вернет указание, нужно ли класс преобразовать в мок-класс
 */
interface AutoloaderFilterInterface
{
    /**
     * Пространство имен PhpMocker-а
     */
    public const PHP_MOCKER_NAMESPACE = 'DraculAid\\PhpMocker';

    /**
     * Список пространств имен PhpUnit библиотеки, и связанных с нею пакетов, которые не должны преобразовываться в мок-классы
     *
     * @todo Изучить, и добавить в этот список значения
     */
    public const PHP_UNIT_NAMESPACES = [
        'PHPUnit'
    ];

    /**
     * Вернет указание, нужно ли класс преобразовать в мок-класс
     *
     * @param   string   $class   Полное имя класса
     * @param   string   $path    Путь к файлу класса
     *
     * @return  bool
     */
    public function canBeMock(string $class, string $path): bool;
}
