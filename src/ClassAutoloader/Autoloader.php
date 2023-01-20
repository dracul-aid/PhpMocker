<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\ClassAutoloader;

use DraculAid\PhpMocker\ClassAutoloader\Drivers\AutoloaderDriverInterface;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderErorrSaveCacheException;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderExceptionInterface;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathException;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathIsNotFileException;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathIsNotReadableException;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathNotFoundException;
use DraculAid\PhpMocker\ClassAutoloader\Filters\AutoloaderFilterInterface;
use DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter;
use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\Exceptions\Creator\HardMockerCreateForInterface;
use DraculAid\PhpMocker\Exceptions\PphMockerExceptionInterface;

/**
 * Автозагрузчик классов, с возможностью при загрузке создавать мок-классы @see HardMocker
 *
 * Оглавление:
 * --- Настройка превращения в мок-классы
 * @see self::$autoMockerEnabled - Ключ, нужно ли все загружаемые классы превращать в мок-классы или нет
 * @see self::$autoloaderFilter - Фильтр, для определения, нужно ли преобразовывать загружаемый класс в мок-класс
 * --- Загрузка классов и поиск путей к ним
 * @see self::getPath() - Вернет путь к загружаемому классу
 * @see self::loadOrException() - Загружает класс, в случае провала выбрасывает исключение
 * @see self::load() - Загружает класс, вернет успех или провал загрузки
 * --- Прочее
 * @see self::$autoloaderDriver [const] - Драйвер "базового автозагрузчика проекта"
 * @see self::$mockClassCachePath [readonly] - Путь к каталогу в котором хранится кеш созданных автозагрузчиком мок-классов (пустая строка, если кеш не используется)
 * @see self::register() - Осущеставляет регистрацию автозагрузчика
 * @see self::unregister() - Снимает регистрацию автозагрузчика
 * --- Управление автозагрузчиками
 * @see Autoloader::$autoloaderList - Список всех созданных объектов-автозагрузчиков
 * @see Autoloader::$allConvertToMock - Указание, что все классы должны быть преобразованны, как мок-классы (без исключения)
 */
class Autoloader
{
    /**
     * Список всех созданных объектов-автозагрузчиков
     *
     * @var Autoloader[]
     */
    public static array $autoloaderList = [];

    /**
     * Указание, что все классы должны быть преобразованны, как мок-классы (без исключения)
     *
     * Может принимать значения:
     *    * NULL: отключено
     *    * TRUE: все загружаемые классы должны быть преобразованы в мок-классы
     *    * FALSE: никакие загружаемые классы не должны быть преобразованы в мок-классы
     *
     * Если установлено TRUE или FALSE автозагрузчик будет игнорировать:
     * @see self::$autoMockerEnabled
     * @see self::$autoloaderFilter
     */
    public static null|bool $allConvertToMock = null;

    /**
     * Путь к каталогу в котором хранится кеш созданных автозагрузчиком мок-классов (пустая строка, если кеш не используется)
     */
    readonly public string $mockClassCachePath;

    /**
     * Драйвер "базового автозагрузчика проекта"
     */
    readonly public AutoloaderDriverInterface $autoloaderDriver;

    /**
     * Фильтр, для определения, нужно ли преобразовывать загружаемый класс в мок-класс
     *
     * @var AutoloaderFilterInterface|DefaultAutoloaderFilter $autoloaderFilter
     */
    public AutoloaderFilterInterface $autoloaderFilter;

    /**
     * Ключ, нужно ли все загружаемые классы превращать в мок-классы или нет
     */
    public bool $autoMockerEnabled = true;

    /**
     * @param   AutoloaderDriverInterface   $autoloaderDriver     Драйвер "базового автозагрузчика проекта"
     * @param   AutoloaderFilterInterface   $autoloaderFilter     Фильтр, для определения, нужно ли преобразовывать загружаемый класс в мок-класс
     * @param   string                      $mockClassCachePath   Путь к каталогу в котором хранится кеш созданных автозагрузчиком мок-классов (пустая строка, если кеш не используется)
     */
    public function __construct(AutoloaderDriverInterface $autoloaderDriver, AutoloaderFilterInterface $autoloaderFilter, string $mockClassCachePath)
    {
        $this->autoloaderDriver = $autoloaderDriver;
        $this->autoloaderFilter = $autoloaderFilter;
        $this->mockClassCachePath = $mockClassCachePath;

        static::$autoloaderList[] = $this;
    }

    /**
     * Вернет путь к загружаемому классу
     *
     * @param   string   $class                Полное имя вызываемого класса (трейта, перечисления или интерфейса)
     * @param   bool     $ifFailToException    Если передать TRUE - в случае провала поиска пути будет выброшено исключение
     *
     * @return  string   Вернет путь к файлу или пустую строку (Если нет файла для загрузки класса)
     *
     * @throws  PhpMockerAutoloaderPathNotFoundException   Если не был найден файл для загрузки класса (и нужно выбросить исключение)
     */
    public function getPath(string $class, bool $ifFailToException = false): string
    {
        $path = $this->autoloaderDriver->getPath($class);

        // * * *

        if ($path !== '') return $path;
        elseif ($ifFailToException) throw new PhpMockerAutoloaderPathNotFoundException($class);
        else return '';
    }

    /**
     * Загрузит класс, в случае провала загрузки будет выброшено исключение
     *
     * @param   string   $class  Полное имя вызываемого класса (трейта, перечисления или интерфейса)
     *
     * @return  void
     *
     * @throws  PhpMockerAutoloaderExceptionInterface       При провале загрузки класса
     * @throws  PphMockerExceptionInterface                 В случаях, если возникли проблемы при создании мок-класса
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  При провале записи PHP кода мок-класса в кеш
     */
    public function loadOrException(string $class): void
    {
        $this->executingClassLoad($class, true);
    }

    /**
     * Загрузит класс и вернет успех или провал загрузки
     *
     * @param   string   $class  Полное имя вызываемого класса (трейта, перечисления или интерфейса)
     *
     * @return  bool   Вернет TRUE если класс был успешно загружен
     *
     * @throws  PphMockerExceptionInterface                 В случаях, если возникли проблемы при создании мок-класса
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  При провале записи PHP кода мок-класса в кеш
     */
    public function load(string $class): bool
    {
        return $this->executingClassLoad($class, false);
    }

    /**
     * Регистрирует автозагрузчик классов
     *
     * @param   bool    $prepend   TRUE поместит автозагрузчик в "начало очереди" и FALSE - в конец
     *
     * @return  void
     */
    public function register(bool $prepend): void
    {
        spl_autoload_register([$this, 'loadOrException'], true, $prepend);
    }

    /**
     * Снимает регистрацию автозагрузчика
     *
     * @return void
     */
    public function unregister(): void
    {
        spl_autoload_unregister([$this, 'loadOrException']);
    }

    /**
     * Выполнит загрузку класса, если надо создав мок-класс
     *
     * @param   string   $class                Полное имя вызываемого класса (трейта, перечисления или интерфейса)
     * @param   bool     $ifFailToException    В случае провала загрузки, нужно ли выбрасывать исключения
     *
     * @return  bool   Вернет TRUE в случае успешной загрузки класса, или FALSE в противном случае
     *
     * @throws  PphMockerExceptionInterface                 В случаях, если возникли проблемы при создании мок-класса
     * @throws  PhpMockerAutoloaderPathException            В случаях, если возникли проблемы при получении содержимого файла класса
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  При провале записи PHP кода мок-класса в кеш
     */
    protected function executingClassLoad(string $class, bool $ifFailToException): bool
    {
        $classPath = $this->getPath($class, $ifFailToException);

        if ($classPath === '')
        {
            return false;
        }

        // * * *

        if ($this->canClassLoadAsMock($class, $classPath))
        {
            return $this->executingClassLoadCreateMock($class, $classPath, $ifFailToException);
        }
        else
        {
            return $this->requireClassFile($classPath, $ifFailToException);
        }
    }

    /**
     * Вернет, может ли класс быть загружен как мок-класс
     *
     * @param   string   $class       Полное имя вызываемого класса (трейта, перечисления или интерфейса)
     * @param   string   $classPath   Путь к PHP файлу класса
     *
     * @return  bool
     *
     * Классы PHP мокера не должны загружаться, как мок классы. Так как подобная загрузка приведет к критической ошибке
     */
    protected function canClassLoadAsMock(string $class, string $classPath): bool
    {
        return static::$allConvertToMock ?? ($this->autoMockerEnabled && $this->autoloaderFilter->canBeMock($class, $classPath));
    }

    /**
     * Подключает файл с описанием класса
     *
     * @param   string   $classPath            Путь к файлу, содержащему код класса
     * @param   bool     $ifFailToException    В случае провала загрузки, нужно ли выбрасывать исключения
     *
     * @return  bool   Может вернуть TRUE в случае успеха загрузки файла
     */
    protected function requireClassFile(string $classPath, bool $ifFailToException): bool
    {
        if (!$ifFailToException && (!is_file($classPath) || !is_readable($classPath)))
        {
            return false;
        }
        else
        {
            require_once($classPath);
            return true;
        }
    }

    /**
     * Создает мок-класс для загружаемого класса
     *
     * @param   string   $class                Загружаемый класс
     * @param   string   $classPath            Путь к файлу, содержащему код класса
     * @param   bool     $ifFailToException    В случае провала загрузки, нужно ли выбрасывать исключения
     *
     * @return  bool   Может вернуть TRUE если мок-класс был создан и инициализирован успешно
     *
     * @throws  PphMockerExceptionInterface                 В случаях, если возникли проблемы при создании мок-класса
     * @throws  PhpMockerAutoloaderPathException            В случаях, если возникли проблемы при получении содержимого файла класса
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  При провале записи PHP кода мок-класса в кеш
     */
    protected function executingClassLoadCreateMock(string $class, string $classPath, bool $ifFailToException): bool
    {
        return AutoloaderMockCreator::exe($this, $class, $classPath, $ifFailToException);
    }
}
