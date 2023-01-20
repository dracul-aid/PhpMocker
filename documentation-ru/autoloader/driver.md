# PhpMocker - Автозагрузчик - Драйвера поиска путей к скрипту класса
[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)

Ресурсы:
* `\DraculAid\PhpMocker\ClassAutoloader\Drivers\AutoloaderDriverInterface` Интерфейс драйверов поиска путей
* `\DraculAid\PhpMocker\ClassAutoloader\Drivers\ComposerAutoloaderDriver` Драйвер "по умолчанию" (для работы с автозагрузчиком композера)

По умолчанию в качестве драйвера используется класс взаимодействующий с автозагрузчиком композера. Если ваш проект не
использует его, то [Настройщик подключения автозагрузчика](autoloader-init.md) позволяет явно указать любой другой драйвер

Класс-драйвер должен следовать интерфейсу `\DraculAid\PhpMocker\ClassAutoloader\Drivers\AutoloaderDriverInterface` и реализовывать:
* `getPath()` Функция поиска пути до файла класса
* `unregister()` Функция снятия регистрации вашего базового автозагрузчика класса из списка автозагрузчиков PHP

---

[<< Оглавление](../README.md) | [Общее об автозагрузке](README.md)
