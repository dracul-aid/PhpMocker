# PhpMocker - Структура кода библиотеки
* `ClassAutoloader` Классы реализующие механизм автозагрузчика классов
  * `ClassAutoloader/Drivers` Классы для взаимодействия с базовыми автозагрузчиками (например, с автозагрузчиком композера)
  * `ClassAutoloader/Exceptions` Исключения, используемые в автозагрузчике классов
  * `ClassAutoloader/Autoloader.php` Класс-автозагрузчика, позволяющего превращать классы в *мок-классы* в момент их загрузки
  * `ClassAutoloader/AutoloaderInit.php` Класс, позволяющий упростить механизм подключения автозагрузчика классов **PhpMocker**
* `CodeGenerator` Генератор PHP кода классов по схемам классов
* `CreateOptions` Классы, для тонкой настройки создаваемых мок-классов
* `Creator` Функционал по созданию мок-классов
* `Exceptions` Исключения используемые в библиотеке
  * [Описание модели исключений](Exceptions/README.md)
* `Managers` Классы-менеджеры, позволяющие взаимодействовать с мок-классами, объектами и методами
* `Reader` Механизм создания схем классов, как с помощью чтения PHP кода, так и с помощью рефлексии
* `Schemes` Схемы классов, используются для полного описания классов
* `Tools` Различные полезные инструменты:
  * `Tools/CallableObject.php` Класс, позволяет использовать в качестве значений свойств классов любой Callable
  * `Tools/Char.php` Функции для работы с символами строк
  * `Tools/ClassTools.php` Функции для работы с классами
  * `Tools/CreateClassImplementsTraits.php` Механизм создания класса, реализующего трейт или список трейтов
  * `Tools/NotPublicProxy.php` Позволяет облегчить работу с непубличными свойствами в различных средах разработки
* `autoloader.php` Файл, выполнение которого вернет объект настройки автозагрузчика PhpMocker-а, также подключит все необходимые для него файлы (классы)
* `MockCreator.php` Класс с набором методов, для создания мок-классов (как с помощью изменения PHP кода, так и с помощью наследования)
* `MockManager.php` Класс для получения менеджеров для мок-классов и мок-методов
* `NotPublic.php` Класс, для взаимодействия с непубличными элементами класса (методами, свойствами, константами) и создания объектов без вызова или с вызовом не публичных конструкторов
