<?php declare(strict_types=1);

/*
 * This file is part of PhpMocker - https://github.com/dracul-aid/PhpMocker
 *
 * (c) Konstantin Marataev <dracul.aid@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DraculAid\PhpMocker\Reader;

use DraculAid\PhpMocker\Exceptions\Creator\SoftMockClassClassNotFoundException;
use DraculAid\PhpMocker\Exceptions\Reader\ReflectionReaderUndefinedTypeException;
use DraculAid\PhpMocker\Reader\ReflectionReader\ReadMethod;
use DraculAid\PhpMocker\Reader\ReflectionReader\StringTypeFromReflection;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\ClassSchemeType;
use DraculAid\PhpMocker\Schemes\AttributeScheme;
use DraculAid\PhpMocker\Schemes\ConstantScheme;
use DraculAid\PhpMocker\Schemes\PropertyScheme;
use DraculAid\PhpMocker\Schemes\SchemeWithAttributesInterface;
use DraculAid\PhpMocker\Schemes\ViewScheme;

/**
 * Класс-функция, читает с помощью PHP рефлексии класс и создает его схему
 * Основная функция @see ReflectionReader::exe() - Создаст схему класса с помощью рефлексии
 *
 * Оглавление:
 * @see ReflectionReader::exe() - Создаст схему класса с помощью рефлексии
 * @see ReflectionReader::readAttributesFor() - Запишет в указанную схему атрибуты
 */
class ReflectionReader
{
    /**
     * Объект "рефлексия класса"
     *
     * @var \ReflectionClass|\ReflectionEnum $reflection
     */
    private \ReflectionClass $reflection;

    /**
     * Создаваемая схема класса
     */
    private ClassScheme $classScheme;

    /**
     * Создаст схему класса с помощью рефлексии
     *
     * @param   string   $class   Имя класса, трейта, интерфейса или перечисления
     *
     * @return  ClassScheme  Вернет схему класса
     *
     * @throws  ReflectionReaderUndefinedTypeException   Если типы данных были представлены в виде неизвестного типа рефлексии
     * @throws  SoftMockClassClassNotFoundException      Если указанный класс не существует
     */
    public static function exe(string $class): ClassScheme
    {
        $generator = new static($class);

        $generator->runBasic();
        $generator->runInterfaces();
        $generator->runTraits();
        $generator->runConstants();
        $generator->runProperties();
        $generator->runMethods();

        return $generator->classScheme;
    }

    /**
     * Запишет в указанную схему атрибуты
     *
     * @param   SchemeWithAttributesInterface   $scheme       Схема в которую будут записаны атрибуты
     * @param   \ReflectionAttribute[]          $attributes   Список атрибутов
     *
     * @return  void
     */
    public static function readAttributesFor(SchemeWithAttributesInterface $scheme, array $attributes): void
    {
        if (PHP_MAJOR_VERSION < 8) return;

        foreach ($attributes as $attribute)
        {
            $attributeScheme = new AttributeScheme($scheme, $attribute->getName());
            $attributeScheme->arguments = $attribute->getArguments();
            $scheme->attributes[] = $attributeScheme;
        }
    }

    /**
     * @param   string   $class   Имя класса, трейта, интерфейса или перечисления
     *
     * @throws  SoftMockClassClassNotFoundException  Если указанный класс не существует
     */
    private function __construct(string $class)
    {
        try {
            if (PHP_MAJOR_VERSION > 7 && enum_exists($class, false)) $this->reflection = new \ReflectionEnum($class);
            else $this->reflection = new \ReflectionClass($class);
        }
        catch (\ReflectionException $error) {
            throw new SoftMockClassClassNotFoundException($class, $error->getMessage());
        }

        // * * *

        $this->classScheme = new ClassScheme(
            ClassSchemeType::createFromReflection($this->reflection),
            $class,
            $this->reflection->isAnonymous()
        );
    }

    /**
     * Определение базовых свойств класса (родители, финальность, анонимность....)
     *
     * @return void
     *
     * @todo  TODO-PHP8.2: добавить проверку isReadonly для класса
     */
    private function runBasic(): void
    {
        $this->classScheme->isFinal = $this->reflection->isFinal();
        $this->classScheme->isReadonly = false; // Заменить в версии для PHP8.2 - класс может быть "только для чтения"
        $this->classScheme->isInternal = $this->reflection->isInternal();
        $this->classScheme->isAnonymous = $this->reflection->isAnonymous();

        if ($this->classScheme->type->canUseExtends() && $this->reflection->getParentClass() !== false)
        {
            $this->classScheme->parent = "\\{$this->reflection->getParentClass()->getName()}";
        }

        if (PHP_MAJOR_VERSION > 7 && $this->reflection->isEnum() && $this->reflection->isBacked())
        {
            $this->classScheme->enumType = StringTypeFromReflection::exe($this->reflection->getBackingType());
        }
    }

    /**
     * Загрузит интерфейсы для класса
     *
     * @return void
     */
    private function runInterfaces(): void
    {
        if (!$this->classScheme->type->canUseInterfaces())
        {
            return;
        }

        ReflectionReader\InterfacesSearcher::exe($this->reflection, $this->classScheme);
    }

    /**
     * Запишет все трейты объявленные в классе
     *
     * @return void
     */
    private function runTraits(): void
    {
        foreach ($this->reflection->getTraitNames() as $trait)
        {
            $this->classScheme->traits["\\{$trait}"] = "\\{$trait}";
        }
    }

    /**
     * Запишет константы используемые в классе (включая "варианты перечислений")
     *
     * @return void
     */
    private function runConstants(): void
    {
        foreach ($this->reflection->getReflectionConstants() as $reflectionOfConstanta)
        {
            $elementName = $reflectionOfConstanta->getName();
            $this->classScheme->constants[$elementName] = new ConstantScheme($this->classScheme, $elementName, $reflectionOfConstanta->getValue());

            $this->classScheme->constants[$elementName]->view = ViewScheme::createFromReflection($reflectionOfConstanta);
            $this->classScheme->constants[$elementName]->isDefine = $this->classScheme->getFullName() === $reflectionOfConstanta->getDeclaringClass()->getName();

            if (PHP_MAJOR_VERSION > 7)
            {
                $this->classScheme->constants[$elementName]->isEnumCase = $reflectionOfConstanta->isEnumCase();
                self::readAttributesFor($this->classScheme->constants[$elementName], $reflectionOfConstanta->getAttributes());
                $this->classScheme->constants[$elementName]->isFinal = $reflectionOfConstanta->isFinal();
            }
        }
    }

    /**
     * Запишет свойства объявленные в классе
     *
     * @return void
     *
     * @throws  ReflectionReaderUndefinedTypeException   Если типы данных были представлены в виде неизвестного типа
     *
     * Стоит обратить внимание, что невозможно получить значения свойства "по умолчанию", если свойство объявлено
     * в конструкторе и имеет "значение по умолчанию" создаваемый объект, например
     * ```php
     * public function __construct( public \stdClass $var_object = new \stdClass() ) {}
     * ```
     */
    private function runProperties(): void
    {
        if (PHP_MAJOR_VERSION < 8)
        {
            $defaultValueProperties = $this->reflection->getDefaultProperties();
        }

        foreach ($this->reflection->getProperties() as $reflectionOfProperty)
        {
            $elementName = $reflectionOfProperty->getName();
            $this->classScheme->properties[$elementName] = new PropertyScheme($this->classScheme, $elementName);

            if ($reflectionOfProperty->hasType())
            {
                $this->classScheme->properties[$elementName]->type = ReflectionReader\StringTypeFromReflection::exe($reflectionOfProperty->getType());
            }

            $this->classScheme->properties[$elementName]->isStatic = $reflectionOfProperty->isStatic();
            $this->classScheme->properties[$elementName]->isDefine = $this->classScheme->getFullName() === $reflectionOfProperty->getDeclaringClass()->getName();
            $this->classScheme->properties[$elementName]->view = ViewScheme::createFromReflection($reflectionOfProperty);

            if (PHP_MAJOR_VERSION > 7)
            {
                if ($reflectionOfProperty->hasDefaultValue())
                {
                    $this->classScheme->properties[$elementName]->setValue($reflectionOfProperty->getDefaultValue());
                }

                self::readAttributesFor($this->classScheme->properties[$elementName], $reflectionOfProperty->getAttributes());
                $this->classScheme->properties[$elementName]->isInConstruct = $reflectionOfProperty->isPromoted();
                $this->classScheme->properties[$elementName]->isReadonly = $reflectionOfProperty->isReadOnly();
            }
            elseif (isset($defaultValueProperties[$elementName]))
            {
                $this->classScheme->properties[$elementName]->setValue($defaultValueProperties[$elementName]);
            }
        }
    }

    /**
     * Запишет методы объявленные в классе
     *
     * @return void
     *
     * @throws  ReflectionReaderUndefinedTypeException   Если типы данных были представлены в виде неизвестного типа
     */
    private function runMethods(): void
    {
        foreach ($this->reflection->getMethods() as $reflectionOfMethod)
        {
            if ($reflectionOfMethod->isPrivate() && $this->classScheme->getFullName() !== $reflectionOfMethod->getDeclaringClass()->getName()) continue;

            ReadMethod::exe($this->classScheme, $reflectionOfMethod);
        }
    }
}
