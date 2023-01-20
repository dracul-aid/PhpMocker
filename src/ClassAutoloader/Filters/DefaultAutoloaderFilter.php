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

use DraculAid\PhpMocker\ClassAutoloader\Filters\Storages\AutoloaderFilerClassNameStorage;
use DraculAid\PhpMocker\ClassAutoloader\Filters\Storages\AutoloaderFilerNamespaceStorage;

/**
 * Фильтр "по умолчанию", для проверок "нужно ли преобразовывать класс в мок-класс при автозагрузке классов"
 *
 * Приоритет:
 * 1) Белый список имен классов
 * 2) Черный список имен классов
 * 3) Белый список пространств имен классов
 * 4) Черный список пространств имен классов
 *
 * Оглавление:
 * @see self::$classBlackList [const] - Черный Список классов
 * @see self::$classWhiteList [const] - Белый список классов
 * @see self::$namespaceBlackList [const] - Черный список Пространств имен
 * @see self::$namespaceWhiteList [const] - Белый список пространств имен
 * @see self::canBeMock() - Проверяет, можно ли класс преобразовывать в мок класс
 */
class DefaultAutoloaderFilter implements AutoloaderFilterInterface
{
    /**
     * Черный Список классов
     */
    readonly public AutoloaderFilerClassNameStorage $classBlackList;

    /**
     * Белый список классов
     */
    readonly public AutoloaderFilerClassNameStorage $classWhiteList;

    /**
     * Черный список Пространств имен
     */
    readonly public AutoloaderFilerNamespaceStorage $namespaceBlackList;

    /**
     * Белый список пространств имен
     */
    readonly public AutoloaderFilerNamespaceStorage $namespaceWhiteList;

    public function __construct()
    {
        $this->classBlackList = new AutoloaderFilerClassNameStorage();
        $this->classWhiteList = new AutoloaderFilerClassNameStorage();
        $this->namespaceBlackList = new AutoloaderFilerNamespaceStorage();
        $this->namespaceWhiteList = new AutoloaderFilerNamespaceStorage();

        $this->__constructSetDefaultValuesInFilters();
    }

    /**
     *
     */
    public function canBeMock(string $class, string $path): bool
    {
        if ($this->classWhiteList->in($class)) return true;
        elseif ($this->classBlackList->in($class)) return false;
        elseif ($this->namespaceWhiteList->in($class)) return true;
        elseif ($this->namespaceBlackList->in($class)) return false;

        return true;
    }

    /**
     * Добавит в хранилища фильтров значения "по умолчанию"
     *
     * @return void
     */
    protected function __constructSetDefaultValuesInFilters(): void
    {
        $this->namespaceBlackList->add(static::PHP_MOCKER_NAMESPACE);
        $this->namespaceBlackList->addList(static::PHP_UNIT_NAMESPACES);
    }
}
