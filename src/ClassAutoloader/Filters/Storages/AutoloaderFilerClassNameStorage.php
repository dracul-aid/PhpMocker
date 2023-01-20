<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\ClassAutoloader\Filters\Storages;

/**
 * Хранилище списка классов для вариантов фильтров "определения, какие классы нужно преобразовывать в мок-классы"
 *
 * Оглавление:
 * @see self::add() - Добавить в фильтр новый класс
 * @see self::addList() - Добавить в фильтр новые классы из массива
 * @see self::remove() - Удалит из фильтра класс
 * @see self::removeList() - Удалит из фильтра классы переданные в массиве
 * @see self::in() - Проверит, есть ли класс в хранилище
 * @see self::getStorageData() - Вернет массив со всеми классами хранимыми в хранилище
 */
class AutoloaderFilerClassNameStorage extends AbstractAutoloaderFilerStorage
{
    /**
     * Хранимые имена классов
     *
     * @var true[] Массив, ключи - имена классов, значения - всегда TRUE
     */
    private array $storage = [];

    public function add(string $addValue): static
    {
        $this->storage[$addValue] = true;

        return $this;
    }

    public function remove(string $removeValue): static
    {
        unset($this->storage[$removeValue]);

        return $this;
    }

    public function getStorageData(): array
    {
        $tmp = array_keys($this->storage);
        return array_combine($tmp, $tmp);
    }

    public function in(string $value): bool
    {
        return isset($this->storage[$value]);
    }
}
