# Инструменты PhpMocker - Прочие инструменты
[<< Оглавление](../README.md) | [Инструменты](README.md)

## Взаимодействия с классами

### Загружен ли класс (класс, трейт, интерфейс или перечисление)

```php
\DraculAid\PhpMocker\Tools\ClassTools::isLoad($className);
```
В качестве `$className` может быть имя класса, трейты, интерфейса или перечисления. Функция сама определит тип класса
и проведет проверку, был ли он загружен

### Встроенный ли в PHP класс (интерфейс)

```php
\DraculAid\PhpMocker\Tools\ClassTools::isInternal($className);
```

Проверит `$className`, является ли он встроенным в PHP классом или интерфейсом (классом любого другого типа)

### Получение родителей класса (включая трейты и интерфейсы)

```php
use DraculAid\PhpMocker\Tools\ClassParents;

/**
 * @see ClassParents::getAllParents() Вернет все классы-родители, интерфейсы и трейты, рекурсивно.
 * Т.е. если родительские трейты используют трейты, или трейты вызывают другие трейты, они также будут
 * в результатах работы функции
 */
ClassParents::getAllParents($className);

/**
 * @see ClassParents::getWithoutInterfaces() Работает аналогично "возвращению всех родителей", Но в результатах будут
 * Отстуствовать интерфейсы
 * 
 * @see ClassParents::getAllParents() - возвращению всех родителей (см описание выше)
 */
ClassParents::getWithoutInterfaces($className);

/**
 * @see ClassParents::getWithoutInterfaces() Вернет все трейты используемые в классе и родительских классах. Если найденные
 * трейты также вызывают другие трейты, они тоже будут помещены в ответ
 */
ClassParents::getTraits($className);
```

### Создатель класса, имплементатора трейтов

```php
\DraculAid\PhpMocker\Tools\CreateClassImplementsTraits::exe($trait);
\DraculAid\PhpMocker\Tools\CreateClassImplementsTraits::exe($trait, $className);
```

Позволяет создать класс, который имплементирует трейт `$trait`. `$trait` Может быть как строкой с именем трейта, так и
массивом-строк с именами трейтов

Необязательный параметр `$className` позволяет указать имя для создаваемого класса

Функция всегда вернет имя созданного класса, если `$className` не был указан, имя будет создано автоматически

---

[<< Оглавление](../README.md) | [Инструменты](README.md)
