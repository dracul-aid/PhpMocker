# PhpMocker - Модель исключений автозагрузчика

См также [Модель исключений для работы с моками](../../Exceptions/README.md)

Все исключения **Автозагрузчика** наследуют интерфейсу `\DraculAid\PhpMocker\ClassAutoloader\Exceptions\PhpMockerAutoloaderExceptionInterface`

Список исключений:
* `PhpMockerAutoloaderPathException` (наследует от `\RuntimeException`): При возникновении проблем с файлами классов
  * `PhpMockerAutoloaderPathNotFoundException`: В случаях провала поиска пути к файлу класса
  * `PhpMockerAutoloaderPathIsNotFileException`: Путь к файлу класса ведет не к файлу
  * `PhpMockerAutoloaderPathIsNotReadableException`: Нет прав на чтение файла класса

Также, могут быть выброшены исключения при автоматическом создании моков, в этом случаи исключения будут наследовать `\DraculAid\PhpMocker\Exceptions\PphMockerExceptionInterface`