<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\ClassAutoloader\Drivers;

use Composer\Autoload\ClassLoader;

/**
 * Драйвер загрузки классов, т.е. это "фасад" для взаимодействия автозагрузчика PHP мокера и автозагрузчика классов композера
 *
 * Оглавление:
 * @see self::$vendorPath [Readonly] - Путь к каталогу vendor композера
 * @see self::$composerClassLoader [Readonly] - Объект "автозагрузчик классов" композера
 * @see self::getPath() - Вернет путь к загружаемому классу
 * @see self::unregister() - Снятие регистрации "базового автозагрузчика"
 */
class ComposerAutoloaderDriver implements AutoloaderDriverInterface
{
    /**
     * Путь к каталогу vendor композера
     */
    readonly public string $vendorPath;

    /**
     * Объект "автозагрузчик классов" композера
     */
    readonly public ClassLoader $composerClassLoader;

    /**
     * @param   string   $vendorPath   Путь к каталогу vendor композера
     */
    public function __construct(string $vendorPath)
    {
        $this->vendorPath = $vendorPath;
        $this->composerClassLoader = require("{$this->vendorPath}/autoload.php");
    }

    public function getPath(string $class): string
    {
        $path = $this->composerClassLoader->findFile($class);

        return empty($path) ? '' : $path;
    }

    public function unregister(): void
    {
        $this->composerClassLoader->unregister();
    }
}
