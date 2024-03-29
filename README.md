# PhpMocker 0.0.2 - Создание мок-классов в PHP8.1 и выше

```
Если ваш проект использует PHP7.4 - Следует смотерть ветку `master-7`. Эта ветка актуальна под PHP 8.1.0 и выше
```

Страница на [Packagist](https://packagist.org/packages/draculaid/phpmocker) и команды для добавления в композер ([подробней об установке](documentation-ru/install/README.md)):
* для PHP 8.1 и выше `composer require draculaid/phpmocker`
* для PHP 7.4.x - 8.0.x `composer require draculaid/phpmocker dev-master-7`

**Документация и Примеры**
* [Документация](documentation-ru/README.md)
* [Примеры](examples-ru/README.md), включая пример с использованием [автозагрузчика композера и PhpUnit](examples-ru/phpunit-and-composer/README.md)
* [Информация по разработке и улучшению библиотеки](documentation-ru/develop.md)
* [История изменений](documentation-ru/history.md)

---

Библиотека **PhpMocker** позволяет создавать мок-классы (тестовые двойники классов) и использовать мок-методы
(тестовые двойники методов), для:
* Финальных классов
* Перечислений
* Непосредственно для трейтов (а не классов, реализующих трейты)
* Финальных, статических и приватных методов

**PhpMocker** работает вне зависимости от используемого фреймворка юнит тестирования, и даже вообще без него.
Возможность превращать в мок-методы, все методы любых типов классов, достигается благодаря анализу PHP кода и его изменения
в момент загрузки и выполнения кода класса, для этого PhpMocker предоставляет автозагрузчик классов
(работающий как с композером, так и с любым другим автозагрузчиком)

**PhpMocker** также поддерживает создание мок-классов с помощью наследования (для классов, абстрактных классов и трейтов)

**Для мок-методов доступно:**
* Счетчик вызовов
* Назначение ответа для вызова
* Выброс любого исключения
* Отработка пользовательской функции, в том числе эта с заменой ответа метода
* Возврат значения аргументов по ссылке
* Изменения значения аргументов метода

Все перечисленные возможности могут быть назначены для определенных агрументов метода. 

**PhpMocker** также предоставляет возможность взаимодействия с непубличными элементами классов (методами, свойствами и константами)
А также удобный механизм для создания объектов, с назначением свойств. Включая создания объектов, без вызова конструктора, или 
с вызовом не публичных конструторов.

---

### В планах...
* Продолжить работу над документацией
* Мок для функций
* Мок для конструкций include() и require()
