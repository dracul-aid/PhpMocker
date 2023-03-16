# PhpMocker 0.0.1 - Создание мок-классов в PHP7.4

```
Ветка `master-7` - Является релиз версией под PHP7.4, Ветка master - Релиз веткой под PHP8.1
```

```
Ветка под PHP7.4 разрабатывается как "временное решение" по той же причине, почему нет смысл разрабатывать версию под PHP 5.x версии
```

**Документация и Примеры**
* [Документация](documentation-ru/README.md)
* [Примеры](examples-ru/README.md), включая пример с использованием [автозагрузчика композера и PhpUnit](examples-ru/phpunit-and-composer/README.md)

**Ветки разработки**
* [master](https://github.com/dracul-aid/PhpMocker/tree/master) - Релиз ветка под PHP 8.1
* [master-7](https://github.com/dracul-aid/PhpMocker/tree/master-7) - Релиз ветка под PHP 7.4

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

### В ближайших планах
* Версия под PHP 7.4
* Документация (довести нынешний черновик до ума)
* Мок для функций
* Мок для конструкций include() и require()
