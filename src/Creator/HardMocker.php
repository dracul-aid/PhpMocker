<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator;

use DraculAid\PhpMocker\Creator\MockClassInterfaces\HardMockClassInterface;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpCodeWithoutElementsException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpFileIsNotReadableException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpFileNotFoundException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockerCreateForInterface;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassIsInternalException;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassWasLoadedException;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;

/**
 * Создает мок-класс с помощью изменения PHP кода
 *
 * Оглавление:
 * @see HardMocker::createForCode() - Создаст мок-класс(ы) для классов найденных в переданном PHP коде и вернет схему(ы) классов
 * @see HardMocker::createClassFromScript() - Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)
 */
class HardMocker extends AbstractMocker
{
    public static function getTextWhyMethodIsNotMockMethod(string|object $owner, string $methodName): string
    {
        $classReflection = new \ReflectionClass($owner);

        if (!$classReflection->hasMethod($methodName)) return "Method {$methodName}() not found";
        elseif ($classReflection->getMethod($methodName)->isAbstract()) return "Method {$methodName}() is a abstract method";
        else return '';
    }

    /**
     * Создаст мок-класс(ы) для классов найденных в переданном PHP коде и вернет схему(ы) классов
     *
     * @param   string          $phpCode     PHP код с описанием класса (или классов)
     * @param   null|callable   $beforeRun   Функция с настройками создания
     *
     * @return  array|ClassManager   Вернет схему или массив схем классов (ключи - имена классов)
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
    public static function createForCode(string $phpCode, null|callable $beforeRun = null): array|ClassManager
    {
        /** @var ClassManager[] $classManagers - Для накопления результатов работы функции */
        $classManagers = [];

        $classSchemes = PhpReader::CodeToSchemes($phpCode);

        if (count($classSchemes) === 0)
        {
            throw new HardMockClassCreatorPhpCodeWithoutElementsException($phpCode);
        }

        foreach ($classSchemes as $classScheme)
        {
            if ($classScheme->isInternal)
            {
                throw new MockClassCreatorClassIsInternalException($classScheme->type->value, $classScheme->getFullName());
            }
            elseif ($classScheme->type == ClassSchemeType::INTERFACES)
            {
                throw new HardMockerCreateForInterface($classScheme->getFullName());
            }

            // В ходе выполнения в схеме класса ($classScheme) может поменяться имя класса
            // Поэтому присваивание в массив с ответом работы функции должно быть через промежуточный шаг
            $tmp = self::createClassExecuting($classScheme, $beforeRun, $classScheme->getFullName());
            $classManagers[$classScheme->getFullName()] = $tmp;
        }

        if (count($classManagers) === 1) return array_shift($classManagers);
        else return $classManagers;
    }

    /**
     * Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)
     *
     * @param   string          $path        Путь к PHP скрипту
     * @param   null|callable   $beforeRun   Функция с настройками создания
     *
     * @return  array|ClassManager   Вернет схему или массив схем классов (ключи - имена классов)
     *
     * (!) Ограничения и детали по результирующему значению @see self::createForCode()
     *
     * @throws  HardMockClassCreatorPhpCodeWithoutElementsException   В PHP коде не был найден код, для которого можно создать моки (не было определения классов)
     * @throws  MockClassCreatorClassIsInternalException              Попытка переопределить в мок-класс встроенный в PHP класс
     * @throws  HardMockerCreateForInterface                          Если в переданном коде содержатся интерфейсы
     * @throws  MockClassCreatorClassWasLoadedException               Попытка переопределения уже загруженного класса
     * @throws  HardMockClassCreatorPhpFileNotFoundException          Если указанный путь ведет не к файлу
     * @throws  HardMockClassCreatorPhpFileIsNotReadableException     Если указанный путь ведет к файлу, на который нет прав на чтение
     */
    public static function createClassFromScript(string $path, null|callable $beforeRun = null): array|ClassManager
    {
        if (!is_file($path)) throw new HardMockClassCreatorPhpFileNotFoundException($path);
        elseif (!is_readable($path)) throw new HardMockClassCreatorPhpFileIsNotReadableException($path);

        return self::createForCode(
            file_get_contents($path),
            $beforeRun
        );
    }

    /**
     * Создает мок-класс для указанного класса или абстрактного класса
     *
     * @param   ClassScheme     $classScheme      Имя класса для которого создается мок
     * @param   null|callable   $beforeRun        Функция настройки создания
     * @param   string          $classOriginal   Имя класса-оригинала, для которого создавался мок-класс (или список классов (через запятую))
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     *
     * Описание "функций настройки создания" @see CreateOptionsInterface
     */
    protected static function createClassExecuting(ClassScheme $classScheme, null|callable $beforeRun, string $classOriginal): ClassManager
    {
        $generator = new static();
        $generator->classScheme = $classScheme;
        $generator->classOriginal = $classOriginal;

        if (is_callable($beforeRun)) $beforeRun($generator->classScheme, $generator->mockerOptions);

        // * * *

        // создаем мок-класс
        $generator->run();

        return $generator->classManager;
    }

    /**
     * Предварительные операции перед созданием мок-класса
     *
     * @return void
     */
    protected function runStart(): void
    {
        if (!in_array('\\' . HardMockClassInterface::class, $this->classScheme->interfaces))
        {
            $this->classScheme->interfaces[] = '\\' . HardMockClassInterface::class;
        }
    }
}
