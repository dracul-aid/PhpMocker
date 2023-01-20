# PhpMocker - Менеджер Мок-Классов - Поиск и получение менеджеров
[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)

Получить "менеджер мок-класса" можно при [создании мок-класса](../mock-classes/README.md) или обратившись к `ClassManager::getManager()`,
функция примет имя созданного мок-класса и вернет объект "менеджер мок-класса" для него.

Получение менеджера с помощью `ClassManager::getManager()` особенно актуально при использовании [автозагрузчика, с автоматическим
созданием мок-классов](../autoloader/README.md)

В случае, если в `ClassManager::getManager()` был передан не мок-класс, будет выброшено исключение

```php
use Project\Models\Users; // Может быть любой (кроме встроенного в PHP класс)
use DraculAid\PhpMocker\Managers\ClassManager;

/** 
 * Подключение автозагрузчика классов
 * @var \DraculAid\PhpMocker\ClassAutoloader\Autoloader $classAutoloader 
 */
$classAutoloader = require_once('vendor/DraculAid/PhpMocker/autoloader.php')->create();

// * * *

// обращение к классу Users (будет загружен и преобразован в мок-класс)
Users::Login();

// получение менеджера
$usersClassManager = ClassManager::getManager(Users::class);

// * * *

// Будет выброшено исключение, встроенный класс stdClass не является мок-классом
ClassManager::getManager(\stdClass::class);
```

--- 

[<< Оглавление](../../README.md) | [Менеджер мок-классов](README.md)
