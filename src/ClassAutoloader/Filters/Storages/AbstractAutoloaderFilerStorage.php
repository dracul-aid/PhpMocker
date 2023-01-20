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
 * Интерфейс, хранилищ вариантов фильтров "определения, какие классы нужно преобразовывать в мок-классы"
 *
 * Оглавление:
 * @see self::add() - Добавить в фильтр новое значение
 * @see self::addList() - Добавить в фильтр новые значения из массива
 * @see self::remove() - Удалит из фильтра значение
 * @see self::removeList() - Удалит из фильтра значения переданные в массиве
 * @see self::in() - Проверит, есть ли значение в хранилище
 * @see self::getStorageData() - Вернет все значения хранилища
 */
abstract class AbstractAutoloaderFilerStorage
{
    /**
     * Добавить в фильтр новое значение
     *
     * @param   string   $addValue
     *
     * @return  $this
     */
    abstract public function add(string $addValue): static;

    /**
     * Добавить в фильтр список новых значений
     *
     * @param   string[]   $addValues
     *
     * @return  $this
     */
    public function addList(array $addValues): static
    {
        foreach ($addValues as $value) $this->add($value);

        return $this;
    }

    /**
     * Удалит из фильтра значение
     *
     * @param   string   $removeValue
     *
     * @return  $this
     */
    abstract public function remove(string $removeValue): static;

    /**
     * Удалит из фильтра значения переданные в массиве
     *
     * @param   string[]   $removeValues
     *
     * @return  $this
     */
    public function removeList(array $removeValues): static
    {
        foreach ($removeValues as $value) $this->remove($value);

        return $this;
    }

    /**
     * Вернет все значения хранилища
     *
     * @return array
     */
    abstract public function getStorageData(): array;

    /**
     * Проверяет, есть ли значение в фильтре
     *
     * @param   string   $value
     *
     * @return  bool
     */
    abstract public function in(string $value): bool;
}
