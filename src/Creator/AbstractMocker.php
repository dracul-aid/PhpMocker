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
use DraculAid\PhpMocker\Creator\Events\BeforeCreateMockClassHandler;
use DraculAid\PhpMocker\Creator\MockerOptions\AbstractMockerOptions;
use DraculAid\PhpMocker\Creator\Services\GeneratorNoPublicMethods;
use DraculAid\PhpMocker\Creator\Services\UpdateMethods;
use DraculAid\PhpMocker\Exceptions\Creator\BeforeCreateMockClassStopException;
use DraculAid\PhpMocker\Exceptions\Creator\MockClassCreatorClassWasLoadedException;
use DraculAid\PhpMocker\Managers\Tools\ClassManagerWithPhpCode;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Tools\ClassTools;

/**
 * Абстрактный класс для создания моков для классов (трейтов, перечислений...)
 *
 * Потомки должны определять:
 * @property   AbstractMockerOptions   $mockerOptions    Хранит объект с параметрами создания мок-классов
 *
 * Оглавление:
 * @see AbstractMocker::NEW_LINE_FOR_METHOD_CODE - Строка, с которой начинается вывод кода (и генерация кода) для методов класса
 * @see AbstractMocker::getTextWhyMethodIsNotMockMethod() Вернет текст с описанием, почему метод не может быть мок-методом
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
     *
     * Класс объектов хранимых в $classManager определяется в {@see self::$create}
     *
     * @var ClassManager|ClassManagerWithPhpCode $classManager
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
     * TRUE если нужно выполнить создание мок-класса или FALSE - создать PHP код мок-класса и записать его в "менеджер мок-класса"
     *
     * Если $create === FALSE, созданный PHP код мок класса будет помещен в {@see ClassManagerWithPhpCode::$createPhpCode}
     * Кроме того, возвращенные функцией "менеджеры мок-класса" будут объектами {@see ClassManagerWithPhpCode}
     */
    protected bool $create = true;

    /**
     * Уникальный идентификатор генерации мок-класса
     */
    readonly protected string $index;

    /**
     * Создать конструктор мок классов напрямую нельзя
     * new static()
     */
    protected function __construct()
    {
        $this->index = uniqid();
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
     * @throws  BeforeCreateMockClassStopException       Создание было остановлено событием "перед созданием мок-класса"
     */
    protected function run(): void
    {
        $this->runStart();

        // * * *

        if (ClassTools::isLoad($this->classScheme->getFullName()))
        {
            throw new MockClassCreatorClassWasLoadedException($this->classScheme->type->value, $this->classScheme->getFullName());
        }

        $this->createClassManager();

        if ($this->classScheme->type !== ClassSchemeType::INTERFACES)
        {
            UpdateMethods::exe($this->classScheme, $this->classManager);
            GeneratorNoPublicMethods::exe($this->classScheme, $this->classManager->index);
        }

        // * * *

        $phpCode = ClassGenerator::generatePhpCode($this->classScheme);
        // echo $phpCode; die();

        /** Может закончиться выбрасыванием исключения: {@see BeforeCreateMockClassStopException} */
        BeforeCreateMockClassHandler::exe($this->classScheme, $phpCode, static::class);

        if ($this->create) eval($phpCode);
        else $this->classManager->createPhpCode = $phpCode;
    }

    /**
     * Создает "менеджер мок-класса" для создаваемого мок-класса
     *
     * @return void
     */
    protected function createClassManager(): void
    {
        $classManager = $this->create ? ClassManager::class : ClassManagerWithPhpCode::class;

        $this->classManager = new $classManager($this->classScheme->type, $this->classScheme->getFullName(), static::class, $this->index);
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
     * Описание "функций настройки создания" {@see CreateOptionsInterface}
     *
     * @param   ClassScheme         $classScheme      Имя класса для которого создается мок
     * @param   null|callable       $beforeRun        Функция настройки создания
     * @param   string              $classOriginal    Имя класса-оригинала, для которого создавался мок-класс (или список классов (через запятую))
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     */
    abstract protected static function createClassExecuting(ClassScheme $classScheme, null|callable $beforeRun, string $classOriginal): ClassManager;

}
