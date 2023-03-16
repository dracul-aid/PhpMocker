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
use DraculAid\PhpMocker\Creator\MockerOptions\HardMockerOptions;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpCodeWithoutElementsException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpFileIsNotReadableException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockClassCreatorPhpFileNotFoundException;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockerCreateForInterface;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassIsInternalException;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassWasLoadedException;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\Tools\ClassManagerWithPhpCode;
use DraculAid\PhpMocker\Reader\PhpReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;

/**
 * Создает мок-класс с помощью изменения PHP кода
 *
 * Оглавление:
 * --- Создание мок-классов
 * @see HardMocker::createForCode() - Создаст мок-класс(ы) для классов найденных в переданном PHP коде и вернет схему(ы) классов
 * @see HardMocker::createClassFromScript() - Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)
 * --- Прочее
 * @see HardMocker::NEW_LINE_FOR_METHOD_CODE - Строка, с которой начинается вывод кода (и генерация кода) для методов класса
 * @see HardMocker::getTextWhyMethodIsNotMockMethod() Вернет текст с описанием, почему метод не может быть мок-методом
 */
class HardMocker extends AbstractMocker
{
    /**
     * Различные параметры и настройки создания мок-классов
     */
    protected HardMockerOptions $mockerOptions;

    public static function getTextWhyMethodIsNotMockMethod($owner, string $methodName): string
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
     * @param   bool            $create      TRUE для создания мок-класса, FALSE мок-класс не будет создан, а его код будет помещен в "менеджер мок-класа"
     *
     * @return  ClassManager[]   Вернет массив схем классов (ключи - имена классов)
     *
     * $beforeRun может быть любой функцией, но лучше следовать объекту-функции описанной интерфейсом @see CreateOptionsInterface
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
     * Если $create === FALSE, созданный PHP код мок класса будет помещен в @see ClassManagerWithPhpCode::$createPhpCode
     * Кроме того, возвращенные функцией "менеджеры мок-класса" будут объектами @see ClassManagerWithPhpCode
     *
     * @throws  HardMockClassCreatorPhpCodeWithoutElementsException   В PHP коде не был найден код, для которого можно создать моки (не было определения классов)
     * @throws  MockClassCreatorClassIsInternalException              Попытка переопределить в мок-класс встроенный в PHP класс
     * @throws  HardMockerCreateForInterface                          Может быть выброшено: Если в переданном коде содержатся интерфейсы
     * @throws  MockClassCreatorClassWasLoadedException               Попытка переопределения уже загруженного класса
     */
    public static function createForCode(string $phpCode, ?callable $beforeRun = null, bool $create = true): array
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
            // В ходе выполнения в схеме класса ($classScheme) может поменяться имя класса
            // Поэтому присваивание в массив с ответом работы функции должно быть через промежуточный шаг
            $tmp = self::createClassExecuting($classScheme, $beforeRun, $classScheme->getFullName(), $create);
            $classManagers[$classScheme->getFullName()] = $tmp;
        }

        return $classManagers;
    }

    /**
     * Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)
     *
     * @param   string          $path        Путь к PHP скрипту
     * @param   null|callable   $beforeRun   Функция с настройками создания
     *
     * @return  ClassManager[]   Вернет массив схем классов (ключи - имена классов)
     *
     * $beforeRun может быть любой функцией, но лучше следовать объекту-функции описанной интерфейсом @see CreateOptionsInterface
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
    public static function createClassFromScript(string $path, ?callable $beforeRun = null): array
    {
        if (!is_file($path)) throw new HardMockClassCreatorPhpFileNotFoundException($path);
        elseif (!is_readable($path)) throw new HardMockClassCreatorPhpFileIsNotReadableException($path);

        return self::createForCode(
            file_get_contents($path),
            $beforeRun
        );
    }

    protected function __construct()
    {
        parent::__construct();

        $this->mockerOptions = new HardMockerOptions();
    }

    /**
     * Создает мок-класс для указанного класса или абстрактного класса
     *
     * @param   ClassScheme     $classScheme      Имя класса для которого создается мок
     * @param   null|callable   $beforeRun        Функция настройки создания
     * @param   string          $classOriginal    Имя класса-оригинала, для которого создавался мок-класс (или список классов (через запятую))
     * @param   bool            $create           TRUE для создания мок-класса, FALSE мок-класс не будет создан, а его код будет помещен в "менеджер мок-класа"
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     *
     * Описание "функций настройки создания" @see CreateOptionsInterface
     *
     * Если $create === FALSE, созданный PHP код мок класса будет помещен в @see ClassManagerWithPhpCode::$createPhpCode
     * Кроме того, возвращенные функцией "менеджеры мок-класса" будут объектами @see ClassManagerWithPhpCode
     */
    protected static function createClassExecuting(ClassScheme $classScheme, ?callable $beforeRun, string $classOriginal, bool $create = true): ClassManager
    {
        $generator = new static();
        $generator->classScheme = $classScheme;
        $generator->classOriginal = $classOriginal;
        $generator->create = $create;

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
     *
     * @throws  MockClassCreatorClassIsInternalException    Попытка переопределить в мок-класс встроенный в PHP класс
     * @throws  HardMockerCreateForInterface                Может быть выброшено: Если в переданном коде содержатся интерфейсы
     */
    protected function runStart(): void
    {
        if ($this->classScheme->isInternal)
        {
            throw new MockClassCreatorClassIsInternalException($this->classScheme->type->value, $this->classScheme->getFullName());
        }
        elseif ($this->mockerOptions->exceptionForInterface && $this->classScheme->type == ClassSchemeType::INTERFACES())
        {
            throw new HardMockerCreateForInterface($this->classScheme->getFullName());
        }

        // * * *

        if (!in_array('\\' . HardMockClassInterface::class, $this->classScheme->interfaces))
        {
            $this->classScheme->interfaces[] = '\\' . HardMockClassInterface::class;
        }
    }
}
