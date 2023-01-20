# PhpMocker - Имя для мок-класса
[<< Оглавление](../../README.md) | [Параметры создания мок-классов](README.md)

Класс `\DraculAid\PhpMocker\CreateOptions\ClassName` позволяет указать, под каким именем нужно создать мок-класс

При использовании этого класса, имя класса будет изменено полностью (т.е. включая пространство имен класса)
```php
use DraculAid\PhpMocker\CreateOptions\ClassName;
use DraculAid\PhpMocker\MockCreator;

$phpCode = 'class OldNameClass {}';

// Созданный класс будет иметь полное имя OldNameClass
$classManager = MockCreator::hardFromPhpCode($phpCode);
// Созданный класс будет иметь полное имя new_class_name
$classManager = MockCreator::hardFromPhpCode($phpCode, new ClassName('new_class_name'));
// Созданный класс будет иметь полное имя my_namespace\new_class_name
$classManager = MockCreator::hardFromPhpCode($phpCode, new ClassName('my_namespace\\new_class_name'));
```

## Проблема, с созданием мок-классов для кода с несколькими классами
Этот класс, не стоит использовать, при создании мок-классов для PHP кода (в том числе и файла-скрипта), в котором
есть описание нескольких классов. В таком случае произойдет:
1) Первый класс будет создан с новым именем
2) При создании второго класса ему будет назначено то же имя, что и первому классу. Это приведет к критической ошибке!

Для таких случаев есть [Класс установки имен для списка классов](rename-list.md)

---

[<< Оглавление](../../README.md) | [Параметры создания мок-классов](README.md)
