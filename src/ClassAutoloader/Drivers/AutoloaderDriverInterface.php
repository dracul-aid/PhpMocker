<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\ClassAutoloader\Drivers;

/**
 * Абстрактный класс драйверов "базовых автозагрузчиков"
 * (позволяет получить путь к загружаемому классу)
 *
 * Под "базовым автозагрузчиком" понимается автозагрузчик классов, который использовался в проекте, до использования
 * PHP-мокера. Чаще всего - это композер, драйвер для него @see ComposerAutoloaderDriver
 *
 * Оглавление:
 * @see self::getPath() - Вернет путь к загружаемому классу
 * @see self::unregister() - Снятие регистрации "базового автозагрузчика"
 */
interface AutoloaderDriverInterface
{
    /**
     * Вернет путь к загружаемому классу
     *
     * @param   string   $class   Имя загружаемого класса (трейта, интерфейса или перечисления)
     *
     * @return  string
     */
    public function getPath(string $class): string;

    /**
     * Снятие регистрации "базового автозагрузчика"
     *
     * @return void
     */
    public function unregister(): void;
}
