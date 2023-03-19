# PhpMocker - Основные функции для работы с библиотекой
[<< Оглавление](README.md)

**Создание мок-классов (тестовых двойников классов)**

Создание мок-классов [с помощью наследования](mock-classes/from-reflection.md)
* `MockCreator::softClass($class)` Создает мок-класс с помощью наследования
* `MockCreator::softTrait($traits)` Создает мок-класс с реализующий указанные трейты

Создание мок-классов [с помощью изменения PHP кода](mock-classes/from-php.md)
* `MockCreator::hardLoadClass($class)` Вызовет автозагрузку класса с преобразованием в мок-класс и вернет "менеджер мок-класса" для него
* `MockCreator::hardFromPhpCode($phpCode)` Создает мок-класс с помощью изменения PHP кода для указанного PHP кода
* `MockCreator::hardFromScript($scriptPath)` Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)

**Получение менеджеров моков**
* `MockManager::getForClass($mockClass)` Вернет "[менеджер мок-класса](manager-class/README.md)" по имени мок-класса
* `MockManager::getForObject($mockObject)` Вернет "[менеджер мок-объекта](manager-object/README.md)" для мок-объекта
* `MockManager::getForMethod($mockClassOrObject, $methodName)` Вернет "[менеджер мок-метода](manager-method/README.md)"
* `MockManager::getForMethodCase($mockClassOrObject, $methodName)` Вернет [кейс вызова](mock-cases/README.md) "по умолчанию" для мок-метода
* `MockManager::getForMethodCase($mockClassOrObject, $methodName, $caseArguments)` Вернет [кейс вызова](mock-cases/README.md) конкретных аргументов для мок-метода

**Взаимодействие с непубличными элементами классов/объектов**
* `NotPublic::createObject($className, $arguments, $properties)` Создаст объект указанного класса, с вызовом или без вызова конструктора (даже не публичного) и установкой свойств объекта (в том числе и не публичных). [Подробнее](tools/notpublic-create-object.md)
* `NotPublic::instance($classNameOrObject)` Вернет объект для взаимодействия с непубличными элементами класса или объекта
* `NotPublic::readConstant($classNameOrObject, $constName)` Вернет значение константы. [Подробнее](tools/notpublic-constant.md)
* `NotPublic::readProperty($classNameOrObject, $propertyName)` Вернет значение свойства. [Подробнее](tools/notpublic-property.md)
* `NotPublic::writeProperty($classNameOrObject, $propertyName, $newValue)` Запишет значение в указанное свойство. [Подробнее](tools/notpublic-property.md)
* `NotPublic::callMethod($classNameOrObject, $methodName, $arguments)` Выполнит указанный метод. [Подробнее](tools/notpublic-method.md)

**Прочие инструменты**
* `Tools\TestTools::waitThrow($function, $arguments, $throwableName)` Выполнит функцию и проверит, было ли во время ее выполнения выброшено исключение. [Подробнее](tools/tools-testing.md)
--- 

[<< Оглавление](README.md)
