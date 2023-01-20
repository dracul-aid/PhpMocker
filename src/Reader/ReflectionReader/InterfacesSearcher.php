<?php

declare(strict_types=1);

namespace DraculAid\PhpMocker\Reader\ReflectionReader;

use DraculAid\PhpMocker\Exceptions\Reader\ReflectionReaderUndefinedTypeException;
use DraculAid\PhpMocker\Schemes\ClassScheme;

/**
 * Класс-функция, для получения списка интерфейсов из рефлексии (интерфейсов конкретного класса, без интерфейсов его родителей)
 * @see InterfacesSearcher::exe()
 */
class InterfacesSearcher
{
    /**
     * Объект "рефлексия класса"
     */
    private \ReflectionClass $reflection;

    /**
     * Объект "схема класса"
     */
    private ClassScheme $scheme;

    /**
     * Вернет список интерфейсов определенных из рефлексии (т.е. без интерфейсов родителей)
     *
     * @param   \ReflectionClass   $reflectionClass   Объект рефлексии "типа данных"
     * @param   ClassScheme        $classScheme       Объект схема исследуемого класса
     *
     * @return  void
     *
     * @throws  ReflectionReaderUndefinedTypeException  Если типы данных были представлены в виде неизвестного типа
     */
    public static function exe(\ReflectionClass $reflectionClass, ClassScheme $classScheme): void
    {
        $executor = new static($reflectionClass, $classScheme);
        $executor->run();
    }

    /**
     * @param   \ReflectionClass   $reflectionClass   Объект рефлексии "типа данных"
     * @param   ClassScheme        $classScheme       Объект схема исследуемого класса
     */
    private function __construct(\ReflectionClass $reflectionClass, ClassScheme $classScheme)
    {
        $this->reflection = $reflectionClass;
        $this->scheme = $classScheme;
    }


    /**
     * Выполняет поиск интерфейсов для класса
     *
     * @return void
     */
    private function run(): void
    {
        // получение всех интерфейсов которым следует класс
        // запомним все интерфейсы, кроме интерфейсов перечислений
        foreach ($this->reflection->getInterfaceNames() as $interface)
        {
            if ($interface !== \BackedEnum::class && $interface !== \UnitEnum::class)
            {
                $this->scheme->interfaces["\\{$interface}"] = "\\{$interface}";
            }
        }

        // если класс реализует интерфейсы - найдем только его интерфейсы
        if (count($this->scheme->interfaces) > 0)
        {
            // пройдем по списку интерфейсов и удалим те из них, что представляют собой потомков
            foreach ($this->scheme->interfaces as $interface)
            {
                $this->removeInterfacesByParent($interface);
            }

            // удалим интерфейсы упомянутые у классов родителей
            if (count($this->scheme->interfaces) > 0 && $this->scheme->parent !== '') foreach (class_parents($this->scheme->getFullName()) as $parent)
            {
                $this->removeInterfacesByParent($parent);
            }
        }
    }

    /**
     * Получит интерфейс-родитель для класса из схемы и удалит все упомянутые в нем интерфейсы из схемы,
     * рекурсивно повторит для всех своих родителей
     *
     * @param   string   $name   Имя интерфейса или класса
     *
     * @return  void
     */
    private function removeInterfacesByParent(string $name): void
    {
        // получим список интерфейсов у класса
        $interfaceList = class_implements($name);

        // если есть родительские интерфейсы - удалим их из схемы
        if (count($interfaceList) > 0) foreach ($interfaceList as $interface)
        {
            $interface = "\\{$interface}";
            $this->removeInterfacesByParent($interface);
            unset($this->scheme->interfaces[$interface]);
        }
    }
}
