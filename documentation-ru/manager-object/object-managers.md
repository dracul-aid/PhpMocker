# PhpMocker - Менеджер мок-объектов - Получение менеджера
[<< Оглавление](../README.md) | [Менеджер мок-объектов](README.md)

Для каждого мок-объекта существует только 1 менеджер, получить его можно:
* С помощью `ObjectManager::getManager()` (передав в функцию мок-объект)
* При создании объекта с помощью `ClassManager::createObjectAndManager()`

Статический метод `ObjectManager::getManager()` позволяет получить "менеджер мок-объекта" для переданного объекта. Если
переданный объект не был мок-объектом, будет выброшено исключение

```php
use DraculAid\PhpMocker\Managers\ObjectManager;

/** @car object $mockObject Полученный ранее мок-объект */
$mockObject;

// Получение менеджера для мок-объекта
$objectManager = ObjectManager::getManager($mockObject);

// Будет выброшено исключение, так как это попытка получить менеджер не для мок-объекта
ObjectManager::getManager(new \stdClass());
```

---

[<< Оглавление](../README.md) | [Менеджер мок-объектов](README.md)
