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

use DraculAid\PhpMocker\CreateOptions\ClassName;
use DraculAid\PhpMocker\Creator\MockClassInterfaces\SoftMockClassInterface;
use DraculAid\PhpMocker\Creator\MockerOptions\SoftMockerOptions;
use DraculAid\PhpMocker\Creator\Services\GeneratorPhpCode;
use DraculAid\PhpMocker\Exceptions\Creator\SoftMockClassCreatorClassIsFinalException;
use DraculAid\PhpMocker\Exceptions\Creator\SoftMockClassCreatorClassIsNotClassException;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Managers\ClassManager;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Tools\CreateClassImplementsTraits;

/**
 * Создает мок-класс и мок-объекты на основе наследования (Работает с классами, абстрактными классами и трейтами)
 *
 * (!) Не может работать с финальными классами, также не может создать мок-методов для финальных и приватных методов
 * (!) Позволяет создать мок-замены для всех методов класса (трейта), в том числе и для методов полученных с помощью наследования.
 *
 * Оглавление:
 * --- Создание мок-классов
 * @see SoftMocker::createClass() - Создает мок-класс для указанного класса
 * @see SoftMocker::createClassForTraits() - Создает мок-класс реализующий указанный трейт
 * --- Прочее
 * @see SoftMocker::NEW_LINE_FOR_METHOD_CODE - Строка, с которой начинается вывод кода (и генерация кода) для методов класса
 * @see SoftMocker::getTextWhyMethodIsNotMockMethod() Вернет текст с описанием, почему метод не может быть мок-методом
 */
class SoftMocker extends AbstractMocker
{
    /**
     * Различные параметры и настройки создания мок-классов
     */
    readonly protected SoftMockerOptions $mockerOptions;

    public static function getTextWhyMethodIsNotMockMethod(string|object $owner, string $methodName): string
    {
        $classReflection = new \ReflectionClass($owner);

        if (!$classReflection->hasMethod($methodName) || $classReflection->getMethod($methodName)->isPrivate())
        {
            return "Method {$methodName}() not found or it is a private method";
        }
        elseif ($classReflection->getMethod($methodName)->isFinal())
        {
            return "Method {$methodName}() is a final method";
        }
        else
        {
            return '';
        }
    }

    /**
     * Создает мок-класс для указанного класса, абстрактного класса или анонимного класса
     *
     * Описание "функций настройки создания" {@see CreateOptionsInterface}
     *
     * (!) Создание класса происходит с использованием механизма наследования
     * (!) Нельзя создать моки для финальных классов, перечислений и интерфейсов, для трейтов {@see SoftMocker::createClassForTraits()}
     * (!) Не могут быть созданы моки для финальных и private методов
     * (!) Если класс имел private конструктор, то при создании объекта мок-класса этот конструктор не будет вызван. А при создании мок-класса будет выброшено предупреждение
     * (!) Класс созданный для абстрактного класса, будет представлять собой реализацию этого абстрактного класса (см ниже)
     *
     * (!) Для $beforeRun отлично подходит {@see ClassName} (позволяет заменить имя класса)
     *
     * В случае, если мок-класс создан для абстрактного класса, все абстрактные методы будут реализованны, но в случае их вызова
     * они будут выбрасывать исключение (или не будут, если было установлено какое-либо мок значение для ответа)
     *
     * @param   string          $classOriginal    Имя класса для которого создается мок
     * @param   null|callable   $beforeRun        Функция с настройками создания
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     */
    public static function createClass(string $classOriginal, null|callable $beforeRun = null): ClassManager
    {
        $scheme = ReflectionReader::exe($classOriginal);

        if ($scheme->type !== ClassSchemeType::CLASSES && $scheme->type !== ClassSchemeType::ABSTRACT_CLASSES)
        {
            throw new SoftMockClassCreatorClassIsNotClassException($scheme->type->value, $classOriginal);
        }
        elseif ($scheme->isFinal)
        {
            throw new SoftMockClassCreatorClassIsFinalException($classOriginal);
        }

        // * * *

        return static::createClassExecuting($scheme, $beforeRun, $classOriginal);
    }

    /**
     * Создает мок-класс реализующий указанный трейт(ы)
     *
     * * Ограничения на создание моков {@see self::createClass()}
     * * Для $beforeRun отлично подходит {@see ClassName} (позволяет заменить имя создаваемого мок-класса)
     * * Реализованно с помощью создания мок-класса, который наследует класс реализующий трейт
     * * Если $trait является массивом - этот массив является списком трейтов для реализации
     *
     * @param   string|array    $trait        Имя трейта для реализации или список трейтов для реализации
     * @param   null|callable   $beforeRun    Функция с настройками создания
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     *
     * @example
     * ```php
     * // Создаст:
     * // use trait_1, trait_2;
     * $trait = [ 'trait_1', 'trait_2' ];
     *
     * // Создаст:
     * // use trait_1, trait_2 {trait_2::bigTalk as talk;};
     * $trait = [ 'trait_1', 'trait_2', 'rules' => 'trait_2::bigTalk as talk;' ];
     * ```
     */
    public static function createClassForTraits(string|array $trait, null|callable $beforeRun = null): ClassManager
    {
        $classForTrait = CreateClassImplementsTraits::exe($trait);

        $scheme = ReflectionReader::exe($classForTrait);
        $scheme->traits = [];
        $traitList = is_string($trait) ? $trait : implode(', ', $trait);

        return static::createClassExecuting($scheme, $beforeRun, $traitList);
    }

    protected function __construct()
    {
        parent::__construct();

        $this->mockerOptions = new SoftMockerOptions();
    }

    /**
     * Создает мок-класс для указанного класса или абстрактного класса
     *
     * @param   ClassScheme     $classScheme      Имя класса для которого создается мок
     * @param   null|callable   $beforeRun        Функция настройки создания
     * @param   string          $classOriginal   Имя класса-оригинала, для которого создавался мок-класс (или список классов (через запятую))
     *
     * @return  ClassManager   Вернет объект "менеджер мок-класса"
     *
     * Описание "функций настройки создания" @see CreateOptionsInterface
     */
    protected static function createClassExecuting(ClassScheme $classScheme, null|callable $beforeRun, string $classOriginal): ClassManager
    {
        $generator = new static();
        $generator->classScheme = $classScheme;
        $generator->classOriginal = $classOriginal;

        $generator->classScheme->parent = "\\{$generator->classScheme->getFullName()}";
        if (is_callable($beforeRun)) $beforeRun($generator->classScheme, $generator->mockerOptions);
        else $generator->classScheme->name = ToolsElementNames::mockClassName($generator->index);

        if ($generator->classScheme->type === ClassSchemeType::ABSTRACT_CLASSES) $generator->classScheme->type = ClassSchemeType::CLASSES;

        // * * *

        // создаем мок-класс
        $generator->run();

        return $generator->classManager;
    }

    /**
     * Предварительные операции перед созданием мок-класса
     *
     * @return void
     */
    protected function runStart(): void
    {
        if (!in_array('\\' . SoftMockClassInterface::class, $this->classScheme->interfaces))
        {
            $this->classScheme->interfaces[] = '\\' . SoftMockClassInterface::class;
        }

        // * * *

        if ($this->classScheme->getConstructor()?->isPrivate())
        {
            trigger_error("Class \\{$this->classOriginal} has private __constructor()", E_USER_WARNING);
            unset($this->classScheme['__constructor']);
        }
        elseif ($this->classScheme->getConstructor()?->isAbstract)
        {
            unset($this->classScheme['__constructor']);
        }


        // * * *

        $this->classScheme->constants = [];
        $this->classScheme->properties = [];

        foreach ($this->classScheme->methods as $name => $method)
        {
            if ($method->isPrivate() || $method->isFinal)
            {
                unset($this->classScheme->methods[$name]);
            }
            elseif ($method->isAbstract)
            {
                $method->isAbstract = false;
                $method->innerPhpCode = "/* Method {$name}() was abstract */ throw new \LogicException('Method {$name}() is abstract and value of return did not set');";
            }
            else
            {
                $method->innerPhpCode = GeneratorPhpCode::generateCallParent($method);
            }
        }
    }
}
