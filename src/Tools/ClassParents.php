<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Tools;

/**
 * Класс, с инструментом для получения всех родителей класса (включая трейтов)
 *
 * Оглавление:
 * @see ClassParents::getAllParents() - Вернет всех "родителей" класса (классы, интерфейсы и трейты, включая трейты классов-родителей)
 * @see ClassParents::getWithoutInterfaces() - Вернет для класса все классы-родители и родительские трейты
 * @see ClassParents::getTraits() - Вернет все трейты класса, и классов-родителей
 */
class ClassParents
{
    /**
     * Имя читаемого класса
     */
    private string $className;

    /**
     * Результат работы - Массив с всеми родительскими классами, интерфейсами и трейтами
     *
     * @var string[]
     */
    private array $result = [];

    /**
     * Вернет всех "родителей" класса (классы, интерфейсы и трейты, включая трейты классов-родителей)
     *
     * @param   string   $class   Имя класса
     *
     * @return  string[]  Массив с всеми родительскими классами, интерфейсами и трейтами
     *
     * @throws  \ReflectionException  Если не удалось получить рефлексию класса или родительских классов
     */
    public static function getAllParents(string $class): array
    {
        $reader = new self();
        $reader->className = $class;

        $reader->readParents(true, true);
        $reader->readInterfaces();
        $reader->readTraitsForClass($reader->className);

        return $reader->result;
    }

    /**
     * Вернет для класса все классы-родители и родительские трейты
     *
     * @param   string   $class   Имя класса
     *
     * @return  string[]  Массив с всеми родительскими классами, интерфейсами и трейтами
     *
     * @throws  \ReflectionException  Если не удалось получить рефлексию класса или родительских классов
     */
    public static function getWithoutInterfaces(string $class): array
    {
        $reader = new self();
        $reader->className = $class;

        $reader->readParents(true, true);
        $reader->readTraitsForClass($reader->className);

        return $reader->result;
    }

    /**
     * Вернет все трейты класса, и классов-родителей
     *
     * @param   string   $class   Имя класса
     *
     * @return  string[]  Массив с именами трейтов
     *
     * @throws  \ReflectionException  Если не удалось получить рефлексию класса или родительских классов
     */
    public static function getTraits(string $class): array
    {
        $reader = new self();
        $reader->className = $class;

        $reader->readParents(false, true);
        $reader->readTraitsForClass($reader->className);

        return $reader->result;
    }

    private function __construct() {}

    /**
     * Получит список классов-родителей, а также найдет все используемые в родителях трейты
     *
     * @param   bool   $parentClasses   Если в результат работы нужно добавить классы-родители
     * @param   bool   $traits          Если в результат работы нужно поместить найденные трейты
     *
     * @return  void
     *
     * @throws  \ReflectionException  Если не удалось получить рефлексию класса
     */
    private function readParents(bool $parentClasses, bool $traits): void
    {
        $parents = class_parents($this->className, false);

        foreach ($parents as $name) {
            if ($parentClasses) $this->result[$name] = $name;
            if ($traits) $this->readTraitsForClass($name);
        }
    }

    /**
     * Получит список интерфейсов класса
     *
     * @return void
     */
    private function readInterfaces(): void
    {
        $interfaces = class_implements($this->className, false);
        $this->result += array_combine($interfaces, $interfaces);
    }

    /**
     * Получит для класса список трейтов (рекурсивно)
     *
     * @param   string   $className  Класс, для которого ведется поиск трейтов
     *
     * @return  void
     *
     * @throws  \ReflectionException  Если не удалось получить рефлексию класса
     */
    private function readTraitsForClass(string $className): void
    {
        $reflectionClass = new \ReflectionClass($className);

        foreach ($reflectionClass->getTraitNames() as $traitName)
        {
            $this->result[$traitName] = $traitName;
            $this->readTraitsForClass($traitName);
        }
    }
}
