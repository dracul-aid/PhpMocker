<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Managers\Tools;

/**
 * Аргументы вызова мок-метода
 *
 * Класс используется для "вызова мок-метода" @see HasCalled
 *
 * Оглавление:
 * @see self::count() - Вернет кол-во аргументов
 * @see self::getValue() - Вернет значение аргумента по его имени или позиции
 * @see self::getLink() - Вернет ссылку на аргумент по его имени или позиции
 * @see self::update() - Обновит значение аргумента
 */
class HasCalledArguments implements \Countable, \ArrayAccess, \IteratorAggregate
{
    private const ARGUMENTS_NOT_FOUND_BY_POSITION = 'Arguments #%s not found';
    private const ARGUMENTS_NOT_FOUND_BY_NAME = 'Arguments $%s not found';

    /**
     * Массив ссылок на аргументы функции (с ключами-именами аргументов)
     */
    private array $argumentsByName;

    /**
     * Массив ссылок на аргументы функции (с ключами-позициями аргументов, начиная с 0-ля)
     */
    private array $argumentsByPosition;

    public function __construct(array $arguments)
    {
        $this->argumentsByName = $arguments;
        $this->argumentsByPosition = array_values($arguments);
    }

    /**
     * Вернет кол-во аргументов
     */
    public function count(): int
    {
        return count($this->argumentsByName);
    }

    /**
     * Вернет значение аргумента по его имени или позиции. Если аргумент не найден выброшено исключение
     *
     * @param   int|string   $index   Имя или позиция аргумента (с 0-ля)
     *
     * @return  mixed
     *
     * @throws  \TypeError   Аргумент с таким именем или позицией не был найден
     */
    public function getValue(int|string $index): mixed
    {
        if (is_string($index))
        {
            $this->issetByName($index, true);
            return $this->argumentsByName[$index];
        }
        else
        {
            $this->issetByPosition($index, true);
            return $this->argumentsByPosition[$index];
        }
    }

    /**
     * Вернет значение аргумента по его имени или позиции. Если аргумент не найден - вернет NULL
     *
     * @param   int|string   $index   Имя или позиция аргумента (с 0-ля)
     *
     * @return  mixed
     */
    public function getValueOrNull(int|string $index): mixed
    {
        if (is_string($index)) return $this->argumentsByName[$index];
        else return $this->argumentsByPosition[$index];
    }

    /**
     * Вернет ссылку на аргумент по его имени или позиции
     *
     * (!) Внимание, для того, что бы принять ссылку, необходимо выставить '&' и перед вызовом функции
     * $firstArgument = &$arguments->getLink(0);
     *
     * @param   int|string   $index   Имя или позиция аргумента (с 0-ля)
     *
     * @return  mixed
     *
     * @throws  \TypeError   Аргумент с таким именем или позицией не был найден
     */
    public function &getLink(int|string $index): mixed
    {
        if (is_string($index))
        {
            $this->issetByName($index, true);
            return $this->argumentsByName[$index];
        }
        else
        {
            $this->issetByPosition($index, true);
            return $this->argumentsByPosition[$index];
        }
    }

    /**
     * Обновит значение аргумента или аргументов
     *
     * (!) Если $index массив, то функция рекурсивно вызывает саму себя. Используя ключи $index, как имя или позицию аргумента
     *
     * @param   int|string|array   $index      Имя или позиция аргумента. Также может быть массивом аргументов для массового изменения
     * @param   mixed              $setData    Новое значение для аргумента
     *
     * @return  mixed
     *
     * @throws  \TypeError  Аргумент с таким именем или позицией не был найден
     */
    public function update(int|string|array $index, mixed $setData = null): static
    {
        if (is_string($index))
        {
            $this->issetByName($index, true);
            $this->argumentsByName[$index] = $setData;
        }
        elseif (is_array($index))
        {
            foreach ($index as $argumentIndex => $argumentValue) $this->update($argumentIndex, $argumentValue);
        }
        else
        {
            $this->issetByPosition($index, true);
            $this->argumentsByPosition[$index] = $setData;
        }

        return $this;
    }

    /**
     * Проверяет, есть ли аргумент с указанным именем или позицией
     *
     * @param   int|string   $index          Имя или позиция аргумента (с 0-ля)
     * @param   bool         $throwIfError   TRUE если нужно выбросить исключение, если аргумент не был найден
     *
     * @return  bool
     */
    public function in(int|string $index, bool $throwIfError = false): bool
    {
        if (is_string($index)) return $this->issetByName($index, $throwIfError);
        else return $this->issetByPosition($index, $throwIfError);
    }

    /**
     * Генератор прохождения по всем аргументам
     *
     * @param   bool   $name   TRUE для "индексов" ввиде имен аргументов или FALSE ввиде позиций аргументов
     *
     * @return  \Generator
     */
    public function for(bool $name): \Generator
    {
        if ($name) $forVar = &$this->argumentsByName;
        else $forVar = &$this->argumentsByPosition;

        foreach ($forVar as $index => $data)
        {
            yield $index => $data;
        }
    }

    /**
     * Проверит, есть ли аргумент с таким именем или позицией
     * 
     * @param   int|string   $offset  Номер позиции аргумента (с 0-ля) или имя аргумента
     *
     * @return  bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->in($offset, false);
    }

    /**
     * Для получения аргумента в стиле работы с массивом
     *
     * @param   int|string   $offset  Номер позиции аргумента (с 0-ля) или имя аргумента
     *
     * @return  mixed
     *
     * @throws  \TypeError   Аргумент с таким именем или позицией не был найден
     */
    public function &offsetGet(mixed $offset): mixed
    {
        return $this->getLink($offset);
    }

    /**
     * Для установки аргументу значения в стиле работы с массивом
     *
     * @param   int|string   $offset   Номер позиции аргумента (с 0-ля) или имя аргумента
     * @param   mixed        $value    Данные для сохранения
     *
     * @return void
     *
     * @throws  \TypeError   Попытка добавить новый аргумент (новый аргумент добавить нельзя)
     * @throws  \TypeError   Аргумент с таким именем или позицией не был найден
     *
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) throw new \TypeError("Value cannot be added into Arguments");

        $this->update($offset, $value);
    }

    /**
     * Осуществляет удаление аргумента в стиле работы с массивами
     * (На самом деле, присваивает аргументу NULL)
     *
     * @param   int|string   $offset  Номер позиции аргумента (с 0-ля) или имя аргумента
     *
     * @return  void
     *
     * @throws  \TypeError   Аргумент с таким именем или позицией не был найден
     */
    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset)) $this->argumentsByName[$offset] = null;
        else $this->argumentsByPosition[$offset] = null;
    }

    /**
     * Проверяет, есть ли аргумент с таким именем
     *
     * @param   string  $name           Имя аргумента
     * @param   bool    $throwIfError   TRUE если нужно выбросить исключение, если аргумент не существует
     *
     * @return  bool
     *
     * @throws  \TypeError   Может быть выброшен: Аргумент с таким именем или позицией не был найден
     */
    private function issetByName(string $name, bool $throwIfError = false): bool
    {
        $issetResult = array_key_exists($name,  $this->argumentsByName);

        if ($throwIfError && !$issetResult) throw new \TypeError(sprintf(self::ARGUMENTS_NOT_FOUND_BY_NAME, $name));

        return $issetResult;
    }

    /**
     * Проверяет, есть ли аргумент с такой позицией
     *
     * @param   int    $position       Позиция аргумента
     * @param   bool   $throwIfError   TRUE если нужно выбросить исключение, если аргумент не существует
     *
     * @return  bool
     *
     * @throws  \TypeError   Может быть выброшен: Аргумент с таким именем или позицией не был найден
     */
    private function issetByPosition(int $position, bool $throwIfError = false): bool
    {
        $issetResult = array_key_exists($position,  $this->argumentsByPosition);

        if ($throwIfError && !$issetResult) throw new \TypeError(sprintf(self::ARGUMENTS_NOT_FOUND_BY_POSITION, $position));

        return $issetResult;
    }

    /**
     * Вернет "Генератор" для прохода по аргументам (индексы - имена аргументов)
     * Используется для перебора аргументов "как массива"
     *
     * @return \Generator
     */
    public function getIterator(): \Generator
    {
        return $this->for(true);
    }
}
