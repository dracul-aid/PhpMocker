# Unit тесты

## Запуск конкретного теста
Запускает тесты из указанного класса
```bash
php tests/run.php tests/Reader/PhpReader/TestCases/ClassOuterReadTest.php
```
Запуск теста, с очисткой предыдущего результата в консоли
```bash
php clear && tests/run.php tests/Reader/PhpReader/TestCases/ClassOuterReadTest.php
```

## Запуск всех тестов
```bash
php tests/run.php tests
```
Запуск теста, с очисткой предыдущего результата в консоли
```bash
clear && php tests/run.php tests
```
