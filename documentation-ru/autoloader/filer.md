# PhpMocker - Автозагрузчик - Фильтры определения, нужно ли конвертировать класс в мок-класс
[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)

Автозагрузчик классов позволяет настроить списки классов (и пространств имен) для которых можно или запрещено выполнять
преобразование в мок-классы.

Алгоритм преобразования выглядит следующим образом:
1) Поиск пути до файла класса
2) Если `Autoloader::$autoMockerEnabled === FALSE` - преобразования не будет 
3) Запрос к `Autoloader::$autoloaderFilter` для определения, можно ли преобразовывать загружаемый класс в мок-класс

`Autoloader::$autoloaderFilter` хранит объект, который получает `имя загружаемого класса` и `путь до файла класса` и возвращает
указание, нужно ли проводить преобразование в мок-класс

Если используется фильтр "по умолчанию" `DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter` то:
1) Классы описанные в пространстве имен `PhpMocker` и библиотеки `PhpUnit` не будут преобразованы
2) Можно добавить классы и пространства имен, в "черные" и "белые" списки, для определения, какие классы могут быть преобразованы, а какие нет

## Фильтр "по умолчанию"

`DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter` фильтр "по умолчанию" состоит из:
1) Белого списка классов
2) Черного списка классов
3) Белого списка пространств имен
4) Черного списка пространств имен

"Списки" указаны по уровню приоритета. "Черные списки" - запрещают преобразование, "Белые" - разрешают

Это значит, что вы можете поместить какое-то пространство имен в "черный список" (по умолчанию в нем находятся
`DraculAid\PhpMocker\` и `PhpUnit`), но, указав класс в белом списке (или подпространство имен) все равно разрешить
его преобразование в мок-класс

### Примеры работы с фильтром "по умолчанию"

```php
/**
 * Объект "Автозагрузчик" (получен выше)
 * @var DraculAid\PhpMocker\ClassAutoloader\Autoloader $autoloader 
 */
$autoloader;

/**
 * Фильтр проверки, нужно ли преобразовывать класс, в мок-класс
 * @see DraculAid\PhpMocker\ClassAutoloader\Filters\DefaultAutoloaderFilter Фильтр "по умолчанию"
 */
$autoloader->autoloaderFilter;

// * * *

// "Белый список классов"
// Классы, хранимые в этом "хранилище" всегда будут преобразованы в мок-классы
$autoloader->autoloaderFilter->classWhiteList;

// "Черный список классов"
// Классы, хранимые в этом "хранилище", не будут преобразованы, если только не указаны в "Белом списке классов"
$autoloader->autoloaderFilter->classBlackList;

// "Белый список пространств имен"
// Классы из этих пространств имен, будут преобразованы в мок-классы, если только не находятся в "черном списке классов"
$autoloader->autoloaderFilter->namespaceWhiteList;

// "Черный список пространств имен"
// Классы из этих пространств имен, не будут преобразованы, если только они не находятся в подпространстве имен
// в "Белом списке пространств имен" или не описаны в "Белом списке классов"
$autoloader->autoloaderFilter->namespaceBlackList;

// * * *

// методы "добавления"/"удаления" общие для всех черных и белых списков

// Добавит класс в белый список
$autoloader->autoloaderFilter->classWhiteList->add($className);

// Удалит класс из черного списка
$autoloader->autoloaderFilter->classBlackList->remove($className);

// Добавит два пространства имен в белый список
$autoloader->autoloaderFilter->namespaceWhiteList->addList([$namespace1, $namespace2]);

// Удалит два пространства имен из черного списка
$autoloader->autoloaderFilter->namespaceBlackList->removeList([$namespace1, $namespace2]);
```

## Свой фильтр

Для своего проекта вы можете создать свой-собственный класс-фильтр. Он должен имплементировать `DraculAid\PhpMocker\ClassAutoloader\Filters\AutoloaderFilterInterface`

Установить фильтр можно:
1) Через настройщик подключения автозагрузчика
2) Записав объект в `Autoloader::$autoloaderFilter`

```php
use  DraculAid\PhpMocker\ClassAutoloader\Filters\AutoloaderFilterInterface;

// "Свой фильтр"
class MyAutoloaderCreateMockFilter implements AutoloaderFilterInterface {}

// * * *

/**
 * Получение "настройщика автозагрузки"
 * @var DraculAid\PhpMocker\ClassAutoloader\AutoloaderInit $autoloaderInit 
 */
$autoloaderInit = require('vendor/DraculAid/PhpMocker/src/autoloader.php');

// указание фильтра автозарузчика
$autoloaderInit->setAutoloaderFilter( new MyAutoloaderCreateMockFilter() );

// Создание и регистрация автозагрузчика
$classAutoloader = $autoloaderInit->create();

// * * *

// смена фильтра в уже созданном автозагрузчике
$classAutoloader->autoloaderFilter = new MyAutoloaderCreateMockFilter();
```

---

[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)
