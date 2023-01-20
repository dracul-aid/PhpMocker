<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Creator;

use DraculAid\PhpMocker\CodeGenerator\ClassGenerator;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassWasLoadedException;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Managers\MethodManager;
use DraculAid\PhpMocker\Tools\ClassTools;

/**
 * Абстрактный класс для создания моков для классов (трейтов, перечислений...)
 *
 */
abstract class AbstractMocker
{
    /**
     * Строка, с которой начинается вывод кода (и генерация кода) для методов класса
     * (используется, для создания форматирования)
     */
    public const NEW_LINE_FOR_METHOD_CODE = "\n" . ClassGenerator::NEW_LINE_FOR_METHOD_CODE;

    /**
     * Хранит схему класса
     */
    protected ClassScheme $classScheme;

    /**
     * Мок-Менеджер создаваемого класса
     */
    protected ClassManager $classManager;

    /**
     * Полное имя оригинального класса, для которого проводится создание мока
     *
     * (!) В некоторых случаях может хранить список классов
     * будет хранить список трейтов для которых создается мок-класс. В таком случае, все классы будут перечисленны через запятую
     */
    protected string $classOriginal;

    /**
     * Уникальный идентификатор генерации мок-класса
     */
    readonly protected string $index;

    /**
     * Различные параметры и настройки создания мок-классов
     */
    readonly protected MockerOptions $mockerOptions;

    /**
     * Создать конструктор мок классов напрямую нельзя
     * new static()
     */
    protected function __construct()
    {
        $this->index = uniqid();
        $this->mockerOptions = new MockerOptions();
    }

    /**
     * Вернет текст с описанием, почему метод не может быть мок-методом
     *
     * @param   string|object    $owner         "Владелец метода" (мок-класс или мок-объект)
     * @param   string           $methodName    Имя метода
     *
     * @return  string   Вернет описание проблемы, или '', если метод может быть мок-методом
     */
    abstract public static function getTextWhyMethodIsNotMockMethod(string|object $owner, string $methodName): string;

    /**
     * Создаст мок-класс
     *
     * @return  void
     *
     * @throws  MockClassCreatorClassWasLoadedException  Если уже объявлен класс с таким-же именем
     */
    protected function run(): void
    {
        $this->runStart();

        // * * *

        if (ClassTools::isLoad($this->classScheme->getFullName()))
        {
            throw new MockClassCreatorClassWasLoadedException($this->classScheme->type->value, $this->classScheme->getFullName());
        }

        $this->classManager = new ClassManager($this->classScheme->getFullName(), static::class, $this->index);

        UpdateMethods::exe($this->classScheme, $this->classManager);
        GeneratorNoPublicMethods::exe($this->classScheme, $this->classManager->index);

        // echo ClassGenerator::generatePhpCode( $this->classScheme );die();
        ClassGenerator::generateCodeAndEval($this->classScheme);
    }


    /**
     * Предварительные операции перед созданием мок-класса
     *
     * @return void
     */
    abstract protected function runStart(): void;

    /**
     * Создает мок-класс для указанного класса, абстрактного класса или анонимного класса
     *
     * @param   ClassScheme         $classScheme      Имя класса для которого создается мок
     * @param   null|callable       $beforeRun        Функция настройки создания
     * @param   string              $classOriginal    Имя класса-оригинала, для которого создавался мок-класс (или список классов (через запятую))
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     *
     * Описание "функций настройки создания" @see CreateOptionsInterface
     */
    abstract protected static function createClassExecuting(ClassScheme $classScheme, null|callable $beforeRun, string $classOriginal): ClassManager;

}
