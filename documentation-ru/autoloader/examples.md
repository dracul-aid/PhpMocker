# PhpMocker - Пример встраивания и работы автозагрузчика
[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)

В каталоге [examples-ru/phpunit-and-composer](../../examples-ru/phpunit-and-composer/README.md) можно найти пример "простого проекта"
в котором реализовано юнит-тестирование с помощью PhpUnit. Для тестирования static методов используется автозагрузчик PhpMocker

## Листинги кода, с описанием, как может подключаться и работать автозагрузчик

### Файл, с подключением автозагрузчика классов

```php
// Путь до каталога vendor
$vendorPath = 'vendor';

// для хранения объекта "PhpMocker автозагрузчика"
// ВНИМАНИЕ: хранение в $GLOBALS - это плохая практика!, тут она используется для упрощения кода.
$GLOBALS['php_mocker_autoloader'] = null;

/**
 * Проверит, похож ли текущий запуск, на запуск юнит тестов
 * 
 * @return bool
 */
function can_be_starting_unit_test(): bool
{
    // если это не запуск в консоле - значит это не юнит тесты
    if (empty($_SERVER['argv'])) return false;
    
    // стоит проверить на наличие подстроки указывающей на тест
    // эта проверка зависит от вашего проекта
    return true;
}

// Если текущий запуск, похож на запуск юнит-тестов
if (can_be_starting_unit_test())
{
    /**
     * Объект "настройщик атозагрузчика" (получен выше)
     * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
     */
    $autoloaderInit = require($vendorPath . '/DraculAid/PhpMocker/src/autoloader.php');
    $autoloaderInit->setComposerVendorPath($vendorPath);
}
// Если текущий запуск не похож на запуск юнит-тестов
else
{
    $composerAutoloader = require("{$vendorPath}}/autoload.php");
}
```

### Класс-посредник, между классом "юнит тестов" PhpUnit и классами конкретных тестов

```php
use PHPUnit\Framework\TestCase;

abstract class AbstractProjectTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        /**
         * Объект "PhpMocker атозагрузчик"
         * @var DraculAid\PhpMocker\ClassAutoloader\Autoloader $autoloader
         */
        $autoloader = $GLOBALS['php_mocker_autoloader'];
    
        parent::__construct($name, $data, $dataName);
        
        // отключение автоматического преобразования в мок-классы
        $autoloader->autoMockerEnabled = false;
    }
}
```

### Класс с юнит тестом

```php
class TestExampleTest extends AbstractProjectTest
{
    public function testRun(): void
    {
        /**
         * Объект "PhpMocker атозагрузчик"
         * @var DraculAid\PhpMocker\ClassAutoloader\Autoloader $autoloader
         */
        $autoloader = $GLOBALS['php_mocker_autoloader'];
        // если в этом тесте загружаемые классы нужно преобразовать в мок-классы
        $autoloader->autoMockerEnabled = true;
        
        /* код юнит-теста */
        
        // по окончанию выполнения теста, лучше отключить преобразование в мок-классы
        // так как оно более не понадобится. Это также слегка улучшит производительность
        $autoloader->autoMockerEnabled = false;
    }
}
```

---

[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)
