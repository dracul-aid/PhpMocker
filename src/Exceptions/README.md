# PhpMocker - Модель исключений

См также [Модель исключений Автозагрузчика классов](../ClassAutoloader/Exceptions/README.md)

Все исключения **PhpMocker** наследуют интерфейсу `\DraculAid\PhpMocker\Exceptions\PphMockerExceptionInterface`

**Основные исключения**

* `Exceptions\AutoloaderNotFoundException` Не был найден автозагрузчки классов PhpMocker-а
* `Exceptions\Creator\AbstractMockClassCreateFailException` Провал создания мок-класса
* `Exceptions\Managers\ManagerNotFoundExceptionInterface` Для случаев, если не был найден запрошенный менеджер (мок-класса, объекта, метода)
  * `Exceptions\Managers\ClassManagerNotFoundException` Если не был найден "менеджер мок-класса"
  * `Exceptions\Managers\ObjectManagerNotFoundException` Если не был найден "менеджер мок-объекта"
  * `Exceptions\Managers\AbstractMethodManagerException` Провал получения "менеджера мок-метода"
* `Exceptions\Reader\AbstractReaderException` Провал создания схемы классов

Также смотрите [Исключения автозагрузчика](../ClassAutoloader/Exceptions/README.md)

**Дерево Исключений**

* `Exceptions\PhpMockerLogicException` (наследует от `\LogicException`)
  * `Exceptions\Managers\MethodIsNotConstructorException` в случаях, если с мок-методом проводится работа, возможная только для конструкторов
* `Exceptions\PhpMockerRuntimeException` (наследует от `\RuntimeException`)
  * **Creator - Исключения связанные с созданием мок-классов**
    * **A:** `Exceptions\Creator\AbstractMockClassCreateFailException` Провал создания мок-класса
      * `Exceptions\Creator\BeforeCreateMockClassStopException` Создание было остановлено событием "перед созданием мок-класса"
      * `Exceptions\Creator\MockClassCreatorClassWasLoadedException`  Попытка переопределения уже загруженного класса
      * `Exceptions\Creator\MockClassCreatorClassIsInternalException` Попытка переопределить в мок-класс с помощью изменения PHP кода встроенный в PHP класс
      * `Exceptions\Creator\HardMockerCreateForInterface` Если в переданном коде PHP содержатся интерфейсы
      * `Exceptions\Creator\SoftMockClassClassNotFoundException` Провал создания мок-класса с помощью наследования, не удалось получить рефлексию для класса
      * `Exceptions\Creator\HardMockClassCreatorPhpCodeWithoutElementsException` В PHP коде не был найден код, для которого можно создать моки (не было определения классов)
      * **A:** `Exceptions\Creator\AbstractHardMockClassCreatorPhpException` Провал при обращении к файлу с описанием класса(ов)
        * `Exceptions\Creator\HardMockClassCreatorPhpFileNotFoundException` Не был найден файл с описанием класса(ов)
        * `Exceptions\Creator\HardMockClassCreatorPhpFileIsNotReadableException` Нет прав на чтение файла с описанием класса(ов) 
  * **Managers - Исключения связанные с работой мок-классов**
    * **I:** `Exceptions\Managers\ManagerNotFoundExceptionInterface` Для случаев, если не был найден менеджер
      * **A:** `Exceptions\Managers\AbstractMethodManagerException` Провал получения "менеджера мок-метода"
        * `Exceptions\Managers\MethodManagerNotFoundException` Не был найден метод
        * `Exceptions\Managers\MethodManagerIncorrectForObjectException` Провал получения "менеджера мок-метода" для объекта, с описанием проблемы
      * `Exceptions\Managers\ClassManagerNotFoundException` Если не был найден "менеджер мок-класса"
      * `Exceptions\Managers\ObjectManagerNotFoundException` Если не был найден "менеджер мок-объекта"
  * **Reader - Исключения связанные с созданием схем классов**
    * **A:** `Exceptions\Reader\AbstractReaderException` Провал создания схемы классов
     * `Exceptions\Reader\PhpReaderUndefinedTypeClassException` Неизвестный тип класса или отсутствие имени класса
     * `Exceptions\Reader\ReflectionReaderUndefinedTypeException` Провал создания строки с типом данных при создании схемы класса из рефлексии
  * `Exceptions\AutoloaderNotFoundException` Не был найден автозагрузчки классов PhpMocker-а
