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

use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderErorrSaveCacheException;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathIsNotFileException;
use DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderPathIsNotReadableException;
use DraculAid\PhpMocker\CreateOptions\ClassAutoloaderOptions;
use DraculAid\PhpMocker\Creator\HardMocker;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\Tools\ClassManagerWithPhpCode;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;

/**
 * Класс-функция для создания мок-класса для загружаемого автозагрузчиком класса - @see AutoloaderMockCreator::exe()
 * (если класс есть в кеше, будет использован кеш)
 *
 * Кеш @see Autoloader::$mockClassCachePath - хранит путь к каталогу с кешем (пустая строка - если кеш не используется)
 *
 */
class AutoloaderMockCreator
{
    protected static ClassAutoloaderOptions $classAutoloaderOptions;

    /**
     * Объект-автозагрузчик классов, который инициировал создание мок-класса
     */
    protected Autoloader $autoloader;

    /**
     * Имя загружаемого класса
     */
    protected string $class;

    /**
     * Путь к файлу, содержащему код класса
     */
    protected string $classPath;

    /**
     * Путь к файлу-кеша
     */
    protected string $cachePath;

    /**
     * В случае провала загрузки, нужно ли выбрасывать исключения
     */
    protected bool $ifFailToException;

    /**
     * Выполнит преобразование класса в мок-класс. Если в кеше есть код мок-класса, будет использован он
     *
     * @param   Autoloader  $autoloader           Объект-автозагрузчик классов, который инициировал создание мок-класса
     * @param   string      $class                Имя загружаемого класса
     * @param   string      $classPath            Путь к файлу, содержащему код класса
     * @param   bool        $ifFailToException    В случае провала загрузки, нужно ли выбрасывать исключения
     *
     * @return  bool
     *
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  При провале записи PHP кода мок-класса в кеш
     */
    public static function exe(Autoloader $autoloader, string $class, string $classPath, bool $ifFailToException): bool
    {
        $executor = new static();
        $executor->autoloader = $autoloader;
        $executor->class = $class;
        $executor->classPath = $classPath;
        $executor->ifFailToException = $ifFailToException;

        // если есть кеш - используем его
        if ($executor->classWithCache())
        {
            $executor->loadClassFromCache();

            return true;
        }
        // если кеша нет - создаем мок-класс, сохраняем его в кеше
        else
        {
            $originalPhpCode = $executor->getOriginalCode();
            if ($originalPhpCode === '') return false;

            $executor->createMockClass($originalPhpCode);

            return true;
        }
    }

    protected function __construct()
    {
        if (!isset(static::$classAutoloaderOptions)) static::$classAutoloaderOptions = new ClassAutoloaderOptions();
    }

    /**
     * Загрузка мок-класса из кеша и создание "менеджера мок-класса"
     *
     * @return void
     */
    protected function loadClassFromCache(): void
    {
        /**
         * Массив с данными для создания "менеджеров мок-методов", описание массива смотри в @see self::saveInCache()
         * @var array $dataForCreateClassManagerList
        */
        $dataForCreateClassManagerList = $this->requireOnce($this->cachePath);
        if (!is_array($dataForCreateClassManagerList))
        {
            throw new \TypeError("Script {$this->cachePath} return is not a Array(), it is " . gettype($dataForCreateClassManagerList));
        }

        foreach ($dataForCreateClassManagerList as $dataForCreate)
        {
            $tmpClassManager = new ClassManager(ClassSchemeType::from($dataForCreate['type']), $dataForCreate['class_name'], $dataForCreate['driver_name'], $dataForCreate['index']);
            $tmpClassManager->setMockMethodNames($dataForCreate['mock_method_names']);
        }
    }

    /**
     * Выполнит подключение указанного файла (с помощью require_once)
     *
     * @param   string   $path   Путь к подключаемому PHP скрипту
     *
     * @return  mixed   Вернет значение, возвращенное require_once
     */
    protected function requireOnce(string $path): mixed
    {
        return require_once($path);
    }

    /**
     * Создает мок-класс и, если надо, сохранит его в кеш
     *
     * @param   string   $originalPhpCode    PHP код оригинального класса
     *
     * @return  void
     *
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  При провале записи PHP кода мок-класса в кеш
     */
    protected function createMockClass(string $originalPhpCode): void
    {
        /**
         * PHP Код для сохранения в кеше
         * @var string $savePhpCode
         */
        $savePhpCode = '';

        /**
         * Массив с описанием данных, достаточных для создания менеджеров мок-классов, описание массива смотри в @see self::saveInCache()
         *
         * @var array $saveCreateManagerData
         */
        $saveCreateManagerData = [];

        // * * *

        /** @var ClassManagerWithPhpCode[] $classManager */
        $classManagers = HardMocker::createForCode($originalPhpCode, static::$classAutoloaderOptions, false);
        $originalPhpCode = '';

        /** @var ClassManagerWithPhpCode $classManager */
        foreach ($classManagers as $classManager)
        {
            if ($this->autoloader->mockClassCachePath !== '')
            {
                $savePhpCode .= "\n\n" . $classManager->createPhpCode;
                $saveCreateManagerData[] = [
                    'type' => $classManager->classType->value,
                    'class_name' => $classManager->toClass,
                    'driver_name' => $classManager->getDriver(),
                    'index' => $classManager->index,
                    'mock_method_names' => $classManager->mockMethodNames,
                ];
            }

            $classManager->evalPhpMockClassCode();
        }

        if ($this->autoloader->mockClassCachePath !== '')
        {
            $this->saveInCache($savePhpCode, $saveCreateManagerData);
        }
    }

    /**
     * Сохраняет созданный мок-клас в кеш
     *
     * $saveCreateManagerData - Представляет собой список, каждый элемент которого массив со следующими элементами:
     *   * 'type': Строка с представлением типа-класса {@see ClassManager::$classType}
     *   * 'class_name': Строка с именем мок-класса {@see ClassManager::$driverName}
     *   * 'driver_name': строка с именем драйвера (класса, создавшего мок-класс) {@see ClassManager::$driverName}
     *   * 'index': уникальный идентификатор мок-класса {@see ClassManager::$index}
     *   * 'mock_method_names': Список имен методов класса, для которых можно получить "мок-метод" {@see ClassManager::$mockMethodNames}
     *
     * @param   string   $phpCode                 PHP код для сохранения
     * @param   array    $saveCreateManagerData   Массив с данными для создания "менеджеров мок-методов"
     *
     * @return  void
     *
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  Если не удалось записать PHP код в файл кеша
     */
    protected function saveInCache(string $phpCode, array $saveCreateManagerData): void
    {
        $this->saveInFile("{$phpCode}\n\nreturn " . var_export($saveCreateManagerData, true) . ';');
    }

    /**
     * Проводит запись PHP кода в файл для @see self::saveInCache()
     *
     * @param   string   $phpCode  Записываемый PHP код
     *
     * @return  void
     *
     * @throws  PhpMockerAutoloaderErorrSaveCacheException  Если не удалось записать PHP код в файл
     */
    protected function saveInFile(string $phpCode): void
    {
        $catalogForCode = dirname($this->cachePath);

        if (!is_dir($catalogForCode))
        {
            if (!mkdir($catalogForCode, 0777, true))
            {
                throw new PhpMockerAutoloaderErorrSaveCacheException($this->cachePath, "Creat catalog {$catalogForCode} was failed");
            }
        }

        if (!file_put_contents($this->cachePath, "<?php\n\n{$phpCode}"))
        {
            throw new PhpMockerAutoloaderErorrSaveCacheException($this->cachePath);
        }
    }

    /**
     * Прочитает код класса
     *
     * @return   string   Вернет PHP код
     *
     * @throws   PhpMockerAutoloaderPathIsNotFileException       Не был найден файл с кодом класса (или путь ведет не на файл)
     * @throws   PhpMockerAutoloaderPathIsNotReadableException   Нет прав на чтение файла с кодом класса
     */
    protected function getOriginalCode(): string
    {
        if (!is_file($this->classPath))
        {
            if ($this->ifFailToException) throw new PhpMockerAutoloaderPathIsNotFileException($this->classPath);
            else return '';
        }
        elseif (!is_readable($this->classPath))
        {
            if ($this->ifFailToException) throw new PhpMockerAutoloaderPathIsNotReadableException($this->classPath);
            else return '';
        }

        // * * *

        return file_get_contents($this->classPath);
    }

    /**
     * Создаст путь до файла кеша мок-класса @see self::$cachePath
     *
     * @return void
     */
    protected function createCachePath(): void
    {
        if (empty($this->cachePath)) $this->cachePath = $this->autoloader->mockClassCachePath . '/' . strtr($this->class, ['\\' => '/']) . '.php';
    }

    /**
     * Вернет указание, есть файл кеша для мок-класса или нет
     * (Вернет FALSE, если кеширование не включено)
     *
     * @return bool
     */
    protected function classWithCache(): bool
    {
        if ($this->autoloader->mockClassCachePath === '') return false;

        $this->createCachePath();

        return is_file($this->cachePath);
    }
}
