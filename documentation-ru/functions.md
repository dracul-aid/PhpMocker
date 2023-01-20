# PhpMocker - Основные функции для работы с библиотекой
[<< Оглавление](../README.md)

**Создание мок-классов (тестовых двойников классов)**

Создание мок-классов [с помощью наследования](mock-classes/from-reflection.md)
* `DraculAid\PhpMocker\MockCreator::softClass($class)` Создает мок-класс с помощью наследования
* `DraculAid\PhpMocker\MockCreator::softTrait($traits)` Создает мок-класс с реализующий указанные трейты

Создание мок-классов [с помощью изменения PHP кода](mock-classes/from-php.md)
* `DraculAid\PhpMocker\MockCreator::hardLoadClass($class)` Вызовет автозагрузку класса с преобразованием в мок-класс и вернет "менеджер мок-класса" для него
* `DraculAid\PhpMocker\MockCreator::hardFromPhpCode($phpCode)` Создает мок-класс с помощью изменения PHP кода для указанного PHP кода
* `DraculAid\PhpMocker\MockCreator::hardFromScript($scriptPath)` Создает мок-класс с помощью изменения PHP кода для указанного файла-скрипта (PHP файла)

**Получение менеджеров моков**
* `DraculAid\PhpMocker\MockManager::getForClass($mockClass)` Вернет "[менеджер мок-класса](manager-class/README.md)" по имени мок-класса
* `DraculAid\PhpMocker\MockManager::getForObject($mockObject)` Вернет "[менеджер мок-объекта](manager-object/README.md)" для мок-объекта
* `DraculAid\PhpMocker\MockManager::getForMethod($mockClassOrObject, $methodName)` Вернет "[менеджер мок-метода](manager-method/README.md)"
* `DraculAid\PhpMocker\MockManager::getForMethodCase($mockClassOrObject, $methodName)` Вернет [кейс вызова](mock-cases/README.md) "по умолчанию" для мок-метода
* `DraculAid\PhpMocker\MockManager::getForMethodCase($mockClassOrObject, $methodName, $caseArguments)` Вернет [кейс вызова](mock-cases/README.md) конкретных аргументов для мок-метода

--- 

[<< Оглавление](../README.md)
