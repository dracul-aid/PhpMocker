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
use DraculAid\PhpMocker\ClassAutoloader\Drivers\ComposerAutoloaderDriver;
use DraculAid\PhpMocker\ClassAutoloader\Filters\AutoloaderFilterInterface;
use DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter;

/**
 * Настройщик создания автозагрузчика классов
 *
 * Оглавление:
 * @see self::create() - Создаст и зарегестрирует автозагрузчик
 * --- Установка настроек
 * @see self::setComposerVendorPath() - Установит путь к каталогу vendor композера (функция не проверяет путь на корректность)
 * @see self::searchAndSetComposerVendorPath() - Пытается найти путь к папке vendor композера
 * @see self::setDriverAutoloaderUnregister() - Нужно ли "выключить" автозагрузку с помощью стандартного автозагрузчика проекта
 * @see self::setMockClassCachePath() - Устанавливае путь для хранения кеша создания мок-классов автозагрузчика
 * @see self::setAutoloaderDriver() - Устанавливает драйвер для поиска путей к вызываемому классу, для автозагрузчика
 * @see self::setAutoloaderFilter() - Устанавливает фильтр, для проверки, нужно ли преобразовывать загружаемый класс, в мок-класс
 */
class AutoloaderInit
{
    /**
     * Нужно ли "выключить" автозагрузку с помощью "драйвера" автозагрузки
     * (обычно это автозагрузчик композера)
     */
    private bool $driverAutoloaderUnregister = true;

    /**
     * Путь к каталогу vendor композера
     *
     * (!) В момент создания объекта, будет произведена попытка автоматически найти путь @see self::__construct()
     */
    private string $composerVendorPath = '';

    /**
     * Объект-драйвер для поиска путей к вызываемому классу, для автозагрузчика
     *
     * NULL - будет использован стандартный драйвер, использующий автозагрузчик композера, @see ComposerAutoloaderDriver
     */
    private null|AutoloaderDriverInterface $autoloaderDriver = null;

    /**
     * Объект-фильтр, для определения, нужно ли преобразовать загружаемый класс в мок-класс
     *
     * NULL - будет использован стандартный фильтр, @see DefaultAutoloaderFilter
     */
    private null|AutoloaderFilterInterface $autoloaderFilter = null;

    /**
     * Путь к каталогу в котором хранится кеш созданных автозагрузчиком мок-классов (пустая строка, если кеш не используется)
     */
    private string $mockClassCachePath = '';

    /**
     * Устанавливает драйвер для поиска путей к вызываемому классу, для автозагрузчика
     *
     * @param   null|AutoloaderDriverInterface   $set  Объект-драйвер или NULL (будет использован стандартный драйвер, использующий автозагрузчик композера)
     *
     * @return  $this
     */
    public function setAutoloaderDriver(null|AutoloaderDriverInterface $set): self
    {
        $this->autoloaderDriver = $set;
        return $this;
    }

    /**
     * Устанавливае путь для хранения кеша создания мок-классов автозагрузчика
     * (пустая строка - если кеш не должен использоваться)
     *
     * @param   string   $path   Путь до каталога кеша (без завещающего слеша, например /web/cache/mocker)
     *
     * @return  $this
     */
    public function setMockClassCachePath(string $path): self
    {
        $this->mockClassCachePath = $path;
        return $this;
    }

    /**
     * Устанавливает фильтр, для проверки, нужно ли преобразовывать загружаемый класс, в мок-класс
     *
     * @param   null|AutoloaderFilterInterface   $set  Объект-фильтр или NULL (будет использован фильтр "по умолчанию")
     *
     * @return  $this
     */
    public function setAutoloaderFilter(null|AutoloaderFilterInterface $set): self
    {
        $this->autoloaderFilter = $set;
        return $this;
    }

    /**
     * Нужно ли "выключить" автозагрузку с помощью стандартного автозагрузчика проекта
     * (обычно, это автозагрузчик композера)
     *
     * @param   bool   $set   TRUE (по умолчанию) автозагрузчик композера будет отключен, FALSE - оставлен
     *
     * @return  $this
     */
    public function setDriverAutoloaderUnregister(bool $set): self
    {
        $this->driverAutoloaderUnregister = $set;
        return $this;
    }

    /**
     * Установит путь к каталогу vendor композера (функция не проверяет путь на корректность)
     * [Актуально, если только базовый автозагрузчик проекта - автозагрузчик композера]
     *
     * @param  string  $path  Путь к каталогу
     *
     * @return  $this
     */
    public function setComposerVendorPath(string $path): self
    {
        $this->composerVendorPath = $path;
        return $this;
    }

    /**
     * Пытается найти путь к папке vendor композера
     * [Актуально, если только базовый автозагрузчик проекта - автозагрузчик композера]
     *
     * @return void
     */
    public function searchAndSetComposerVendorPath(): bool
    {
        $tmp = dirname(__DIR__, 3) . '/vendor';
        if (is_dir($tmp))
        {
            $this->composerVendorPath = $tmp;
            return true;
        }

        $tmp = dirname(__DIR__, 2) . '/vendor';
        if (is_dir($tmp))
        {
            $this->composerVendorPath = $tmp;
            return true;
        }

        return false;
    }


    /**
     * Выполняет создание и регистрацию автозагрузчика
     *
     * @return Autoloader
     */
    public function create(bool $register = true, bool $prepend = false): Autoloader
    {
        if ($this->autoloaderDriver === null) $this->createDefaultAutoloaderDriver();
        if ($this->autoloaderFilter === null) $this->autoloaderFilter = new DefaultAutoloaderFilter();

        // * * *

        $autoloader = new Autoloader($this->autoloaderDriver, $this->autoloaderFilter, $this->mockClassCachePath);

        if ($register) $autoloader->register($prepend);
        if ($this->driverAutoloaderUnregister) $this->autoloaderDriver->unregister();

        return $autoloader;
    }

    /**
     * Создание функции генерации пути к загружаемому классу
     *
     * @return  void
     *
     * @throws  \RuntimeException   Не был установлен каталог к папке Vendor
     */
    private function createDefaultAutoloaderDriver(): void
    {
        if ($this->composerVendorPath === '' && !$this->searchAndSetComposerVendorPath())
        {
            throw new \RuntimeException("Catalog 'Vendor of Composer' was not setted");
        }

        $this->autoloaderDriver = new ComposerAutoloaderDriver($this->composerVendorPath);
    }
}
