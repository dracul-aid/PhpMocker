<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker;

use DraculAid\PhpMocker\ClassAutoloader\Autoloader;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderExceptionInterface;
use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\Creator\SoftMocker;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpCodeWithoutElementsException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpFileIsNotReadableException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpFileNotFoundException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockerCreateForInterface;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassIsInternalException;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassWasLoadedException;
use DraculAid\PhpMocker\Exceptions\PphMockerExceptionInterface;
use DraculAid\PhpMocker\Exceptions\AutoloaderNotFoundException;
use DraculAid\PhpMocker\Managers\ClassManager;

/**
 * Набор функций для создания мок-классов
 *
 * Оглавление:
 * @see MockCreator::VERSION - Версия PHP мокера
 * @see MockCreator::softClass() - Создает мок-класс с помощью наследования
 * @see MockCreator::softTrait() - Создает мок-класс с реализующий указанные трейты
 * @see MockCreator::hardLoadClass() - Вызовет автозагрузку класса с преобразованием в мок-класс и вернет "менеджер мок-класса" для него
 * @see MockCreator::hardFromPhpCode() - Создает мок-класс с помощью изменения PHP кода для указанного PHP кода
 * @see MockCreator::hardFromScript() - Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)
 */
class MockCreator
{
    /**
     * Версия PHP мокера
     */
    public const VERSION = 'PHP7-0.0.1';

    /**
     * Создает мок-класс с помощью наследования
     *
     * @param   string          $classOriginal    Имя класса для которого создается мок
     * @param   null|callable   $beforeRun        Функция с настройками создания
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     *
     * Описание "функций настройки создания" @see CreateOptionsInterface
     *
     * (!) Создание класса происходит с использованием механизма наследования
     * (!) Нельзя создать моки для финальных классов, перечислений и интерфейсов, для трейтов @see MockCreator::softTrait()
     * (!) Не могут быть созданы моки для финальных и private методов
     * (!) Если класс имел private конструктор, то при создании объекта мок-класса этот конструктор не будет вызван. А при создании мок-класса будет выброшено предупреждение
     * (!) Класс созданный для абстрактного класса, будет представлять собой реализацию этого абстрактного класса (см ниже)
     *
     * (!) Для $beforeRun отлично подходит @see ClassName (позволяет заменить имя класса)
     *
     * В случае, если мок-класс создан для абстрактного класса, все абстрактные методы будут реализованны, но в случае их вызова
     * они будут выбрасывать исключение (или не будут, если было установлено какое-либо мок значение для ответа)
     */
    public static function softClass(string $classOriginal, ?callable $beforeRun = null): ClassManager
    {
        return SoftMocker::createClass($classOriginal, $beforeRun);
    }

    /**
     * Создает мок-класс с реализующий указанные трейты
     *
     * @param   string|array    $trait        Имя трейта для реализации или список трейтов для реализации
     * @param   null|callable   $beforeRun    Функция с настройками создания
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     *
     * (!) Ограничения на создание моков @see self::createClass()
     * (!) Для $beforeRun отлично подходит @see ClassName (позволяет заменить имя создаваемого мок-класса)
     * (!) Реализованно с помощью создания мок-класса, который наследует класс реализующий трейт
     *
     * (!) Для $beforeRun отлично подходит @see ClassName (позволяет заменить имя класса)
     *
     * (!) Если $trait является массивом - этот массив является списком трейтов для реализации
     * ```php
     * // Создаст:
     * // use trait_1, trait_2;
     * $trait = [ 'trait_1', 'trait_2' ];
     *
     * // Создаст:
     * // use trait_1, trait_2 {trait_2::bigTalk as talk;};
     * $trait = [ 'trait_1', 'trait_2', 'rules' => 'trait_2::bigTalk as talk;' ];
     * ```
     */
    public static function softTrait($trait, ?callable $beforeRun = null): ClassManager
    {
        return SoftMocker::createClassForTraits($trait, $beforeRun);
    }

    /**
     * Вызовет автозагрузку класса и вернет "менеджер мок-класса" для него
     *
     * Если автозагрузчик PhpMocker-а не подключен, или класс не был преобразован в мок-класс, выбросит исключение
     *
     * Если произведет загрузку класса, то класс и все его родители, будут преобразованы в мок-классы
     *
     * @param   string    $class
     *
     * @return   ClassManager
     *
     * (!) Создаст мок классы для классов, абстрактных классов, трейтов и перечислений (включая финальные классы)
     * (!) Интерфейс будет загружен без преобразования в мок-класс
     *
     * @throws  AutoloaderNotFoundException            В случае если не был найден автозагрузчик PhpMocker-а
     * @throws  PhpMockerAutoloaderExceptionInterface  В случае провала автозагрузки класса
     * @throws  PphMockerExceptionInterface            В случае провала преобразования в мок-класс
     *
     * @todo  Переработать поиск менеджера и вызов загрузки класса (см комментарии в коде)
     */
    public static function hardLoadClass(string $class): ClassManager
    {
        // если уже есть менеджер класса - вернем его
        // если класс уже загружен, как обычный класс - выбросим исключение
        // загрузка класса и его родителей, при загрузке преобразуем загружаемые классы в мок-классы

        if (count(Autoloader::$autoloaderList) === 0)
        {
            throw new AutoloaderNotFoundException($class);
        }

        // * * *

        $oldValueForAllConvertToMock = Autoloader::$allConvertToMock;
        Autoloader::$allConvertToMock = true;

        // вызов автозагрузки класса
        class_exists($class);

        Autoloader::$allConvertToMock = $oldValueForAllConvertToMock;

        return MockManager::getForClass($class);
    }

    /**
     * Создает мок-класс с помощью изменения PHP кода
     *
     * @param   string          $phpCode     PHP код с описанием класса (или классов)
     * @param   null|callable   $beforeRun   Функция с настройками создания
     *
     * @return  ClassManager[]|ClassManager   Вернет схему или массив схем классов (ключи - имена классов)
     *
     * (!) Создаст мок классы для классов, абстрактных классов, трейтов и перечислений (включая финальные классы)
     * (!) Создание мок копии для интерфейсов бесполезно, поэтому не будет производиться
     * (!) Если класс уже инициализирован - выбросит исключение (пересоздание класса под тем же именем невозможно)
     * (!) Невозможно будет создать мок-методы для абстрактных методов
     *
     * RETURN:
     * Если PHP код содержал описание 1-го класса, функция вернет менеджер для этого класса
     * Если код содержал несколько классов, функция вернет массив менеджеров классов (ключи массива - иена классов)
     *
     * @throws  HardMockClassCreatorPhpCodeWithoutElementsException   В PHP коде не был найден код, для которого можно создать моки (не было определения классов)
     * @throws  MockClassCreatorClassIsInternalException              Попытка переопределить в мок-класс встроенный в PHP класс
     * @throws  HardMockerCreateForInterface                          Если в переданном коде содержатся интерфейсы
     * @throws  MockClassCreatorClassWasLoadedException               Попытка переопределения уже загруженного класса
     */
    public static function hardFromPhpCode(string $phpCode, ?callable $beforeRun = null)
    {
        $classManagers = HardMocker::createForCode($phpCode, $beforeRun);

        if (count($classManagers) === 1) return array_shift($classManagers);
        else return $classManagers;
    }

    /**
     * Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)
     *
     * (!) Ограничения и детали по результирующему значению @see self::hardFromPhpCode()
     *
     * @param   string          $path        Путь к PHP скрипту
     * @param   null|callable   $beforeRun   Функция с настройками создания
     *
     * @return  ClassManager[]|ClassManager   Вернет схему или массив схем классов (ключи - имена классов)
     *
     * @throws  HardMockClassCreatorPhpCodeWithoutElementsException   В PHP коде не был найден код, для которого можно создать моки (не было определения классов)
     * @throws  MockClassCreatorClassIsInternalException              Попытка переопределить в мок-класс встроенный в PHP класс
     * @throws  HardMockerCreateForInterface                          Если в переданном коде содержатся интерфейсы
     * @throws  MockClassCreatorClassWasLoadedException               Попытка переопределения уже загруженного класса
     * @throws  HardMockClassCreatorPhpFileNotFoundException          Если указанный путь ведет не к файлу
     * @throws  HardMockClassCreatorPhpFileIsNotReadableException     Если указанный путь ведет к файлу, на который нет прав на чтение
     */
    public static function hardFromScript(string $path, ?callable $beforeRun = null)
    {
        $classManagers = HardMocker::createClassFromScript($path, $beforeRun);

        if (count($classManagers) === 1) return array_shift($classManagers);
        else return $classManagers;
    }
}
