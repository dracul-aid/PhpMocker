# PhpMocker - Настройщик создания автозагрузчика
[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)

Для подключения автозагрузчика необходимо вызвать `autoloader.php` - он вернет объект "настройщик автозагрузчика"

```php
/**
 * Получение "настройщика автозагрузки"
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
 */
$autoloaderInit = require('vendor/DraculAid/PhpMocker/src/autoloader.php');

// Регистрирует PhpMocker автозагрузчик;
// Снимает регистрацию автозагрузчика композера;
// Возвращает объект автозагрузчик (для дальнейшего управления автозагрузкой);
$autoloaderObject = $autoloaderInit->create();
```

## Указание на месторасположение автозагрузчика композера

В большинстве случаев, "настройщик автозагрузчика" сам найдет "автозагрузчик композера", но вы также можете передать его явно

```php
/**
 * Объект "настройщик атозагрузчика" (получен выше)
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
 */
$autoloaderInit->setComposerVendorPath('путь до папки vendor composer-а');

// Регистрирует PhpMocker автозагрузчик;
$autoloaderObject = $autoloaderInit->create();
```

## Запрет на регистрацию автозагрузчика | Запрет на снятия регистрации автозагрузчика композера

По умолчанию, в момент создания автозагрузчика, будет снята регистрация автозагрузчика композера, и в список автозагрузчиков PHP
будет добавлен автозагрузчик PhpMocker

```php
/**
 * Объект "настройщик атозагрузчика" (получен выше)
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
 */
$autoloaderInit;

// Отключит "снятие регистрации автозагрузчика композера"
$autoloaderInit->setDriverAutoloaderUnregister(false);

// Вернет объект автозагрузчик, но НЕ будет регистрировать его в PHP 
$autoloaderObject = $autoloaderInit->create();

// Снимет регистрацию "автозагрузчика композера"
$autoloaderObject->autoloaderDriver->unregister();

// Осуществит регистрацию "PhpMocker автозагрузчика"
$autoloaderObject->register();
```

## Кеш для мок-классов

Автозагрузчик PhpMocker-а может кешировать создание мок-классов, для этого необходимо указать каталог для хранения файлов
кеша - `AutoloaderInit::setMockClassCachePath()`

Подробнее [о кеше](cache.md)

```php
/**
 * Получение "настройщика автозагрузки"
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
 */
$autoloaderInit = require('vendor/DraculAid/PhpMocker/src/autoloader.php');

// Указание каталога для хранения кеша мок-классов
$autoloaderInit->setMockClassCachePath('/tmp/php-mocker/cache-for-mock-class');

// Регистрирует PhpMocker автозагрузчик;
// Снимает регистрацию автозагрузчика композера;
// Возвращает объект автозагрузчик (для дальнейшего управления автозагрузкой);
$autoloaderObject = $autoloaderInit->create();
```

## Нестандартный драйвер поиска файлов классов

Если ваш проект не использует автозагрузчик композера, нужно будет написать класс-драйвер, для поиска путей до файлов
загружаемых классов. Класс-драйвер должен следовать `\DraculAid\PhpMocker\ClassAutoloader\Drivers\AutoloaderDriverInterface`

Подробнее [о Драйвере поиска путей до файлов классов](driver.md)

Для установки драйвера нужно воспользоваться `AutoloaderInit::setAutoloaderDriver()`

```php
/**
 * Объект "настройщик атозагрузчика" (получен выше)
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
 */
$autoloaderInit;

// установка драйвера автозагрузки
$autoloaderInit->setAutoloaderDriver(
    new YourAutoloaderDriver()
);

$autoloaderInit->setAutoloaderFilter()
```

## Фильтр определения, какие классы должны быть преобразованы в мок-класс

Для контроля списка классов (пространств имен) для которых нужно или не нужно проводить преобразование в мок-классы (при их 
автозагрузки), используются филтры.

Используемый по умолчанию фильтр, позволяет создавать "белые" и "черные списки" для классов и пространств имен
* Белые списки - разрешают преобразование 
* Черные списки - запрещают преобразования

Белые списки имеют более высокий приоритет

Подробнее о [Фильтрах](filer.md)

Для установки нестандартного фильтра необходимо воспользоваться `AutoloaderInit::setAutoloaderFilter()`

```php
/**
 * Объект "настройщик атозагрузчика" (получен выше)
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
 */
$autoloaderInit;

// установка фильтра
$autoloaderInit->setAutoloaderFilter(
    new YourAutoloaderFilter()
);
```

---

[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)
