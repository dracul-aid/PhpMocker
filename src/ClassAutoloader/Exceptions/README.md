# PhpMocker - Модель исключений автозагрузчика

См также [Модель исключений для работы с моками](../../Exceptions/README.md)

Все исключения **Автозагрузчика** наследуют интерфейсу `\DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderExceptionInterface`

**Основные исключения**
* `ClassAutoloader\Exceptions\PhpMockerAutoloaderPathException`: При возникновении проблем с файлами классов
* `PClassAutoloader\Exceptions\hpMockerAutoloaderErorrSaveCacheException`: Ошибка сохранения кода мок-класса в кеш

**Дерево исключений:**
* **А:** `ClassAutoloader\Exceptions\PhpMockerAutoloaderPathException` (наследует от `\RuntimeException`): При возникновении проблем с файлами классов
  * `ClassAutoloader\Exceptions\PhpMockerAutoloaderPathNotFoundException`: В случаях провала поиска пути к файлу класса
  * `ClassAutoloader\Exceptions\PhpMockerAutoloaderPathIsNotFileException`: Путь к файлу класса ведет не к файлу
  * `ClassAutoloader\Exceptions\PhpMockerAutoloaderPathIsNotReadableException`: Нет прав на чтение файла класса
* `PClassAutoloader\Exceptions\hpMockerAutoloaderErorrSaveCacheException` (наследует от `\RuntimeException`): Ошибка сохранения кода мок-класса в кеш

Также, могут быть выброшены исключения при автоматическом создании моков, в этом случаи исключения будут наследовать `\DraculAid\PhpMocker\Exceptions\PphMockerExceptionInterface`
