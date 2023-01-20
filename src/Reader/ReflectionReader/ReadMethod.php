<?php

declare(strict_types=1);

namespace DraculAid\PhpMocker\Reader\ReflectionReader;

use DraculAid\PhpMocker\Exceptions\Reader\ReflectionReaderUndefinedTypeException;
use DraculAid\PhpMocker\Reader\ReflectionReader;
use DraculAid\PhpMocker\Schemes\ClassScheme;
use DraculAid\PhpMocker\Schemes\MethodScheme;
use DraculAid\PhpMocker\Schemes\ViewScheme;
use DraculAid\PhpMocker\Schemes\MethodArgumentScheme;

/**
 * Создаст схему метода на основе рефлексии метода
 */
class ReadMethod
{
    /**
     * Рефлексия исследуемого метода
     */
    private \ReflectionMethod $reflectionMethod;

    /**
     * Объект "схема метода", результат работы @see ReadMethod::exe()
     */
    private ClassScheme $scheme;

    /**
     * Создаст схему метода на основе рефлексии метода и поместит ее в схему класса
     *
     * @param   ClassScheme         $classScheme        Схема класса
     * @param   \ReflectionMethod   $reflectionMethod   Рефлексия метода
     *
     * @return  void
     */
    public static function exe(ClassScheme $classScheme, \ReflectionMethod $reflectionMethod): void
    {
        $executor = new static($classScheme, $reflectionMethod);
        $executor->run();
    }

    /**
     * @param   ClassScheme         $classScheme        Схема класса
     * @param   \ReflectionMethod   $reflectionMethod   Рефлексия метода
     */
    private function __construct(ClassScheme $classScheme, \ReflectionMethod $reflectionMethod)
    {
        $this->reflectionMethod = $reflectionMethod;
        $this->scheme = $classScheme;
    }

    /**
     * Выполняет создание схемы метода
     *
     * @return void
     */
    private function run(): void
    {
        $methodName = $this->reflectionMethod->getName();
        $this->scheme->methods[$methodName] = new MethodScheme($this->scheme, $methodName);

        if ($this->reflectionMethod->hasReturnType())
        {
            $this->scheme->methods[$methodName]->returnType =  ReflectionReader\StringTypeFromReflection::exe($this->reflectionMethod->getReturnType());
        }

        ReflectionReader::readAttributesFor($this->scheme->methods[$methodName], $this->reflectionMethod->getAttributes());

        $this->scheme->methods[$methodName]->view = ViewScheme::createFromReflection($this->reflectionMethod);
        $this->scheme->methods[$methodName]->isFinal = $this->reflectionMethod->isFinal();
        $this->scheme->methods[$methodName]->isStatic = $this->reflectionMethod->isStatic();
        $this->scheme->methods[$methodName]->isReturnLink = $this->reflectionMethod->returnsReference();
        $this->scheme->methods[$methodName]->isDefine = $this->scheme->getFullName() === $this->reflectionMethod->getDeclaringClass()->getName();
        $this->scheme->methods[$methodName]->isAnonymous = $this->reflectionMethod->isClosure();
        $this->scheme->methods[$methodName]->isAbstract = $this->reflectionMethod->isAbstract();

        if ($this->reflectionMethod->getNumberOfParameters() > 0)
        {
            $this->runMethodsArguments($this->scheme->methods[$methodName], $this->reflectionMethod);
        }
    }

    /**
     * Запишет аргументы метода
     *
     * @param   MethodScheme         $schemeMethod        Схема метода
     * @param   \ReflectionMethod    $reflectionMethod    Рефлексия метода
     *
     * @return  void
     *
     * @throws  ReflectionReaderUndefinedTypeException   Если типы данных были представлены в виде неизвестного типа
     */
    private function runMethodsArguments(MethodScheme $schemeMethod, \ReflectionMethod $reflectionMethod): void
    {
        foreach ($reflectionMethod->getParameters() as $elementReflection)
        {
            $elementName = $elementReflection->getName();
            $schemeMethod->arguments[$elementName] = new MethodArgumentScheme($schemeMethod, $elementName);

            $this->runMethodsArgumentsSetDefaultValue($schemeMethod->arguments[$elementName], $elementReflection);

            if ($elementReflection->hasType()) $schemeMethod->arguments[$elementName]->type = StringTypeFromReflection::exe($elementReflection->getType());

            $schemeMethod->arguments[$elementName]->isLink = $elementReflection->isPassedByReference();
        }
    }

    /**
     * Запишет для схемы аргумента значение по умолчанию и установит, обязательный это параметр или нет
     *
     * @param   MethodArgumentScheme   $schemeArgument       Объект схема аргумента
     * @param   \ReflectionParameter   $reflectionArgument   Объект рефлексия аргумента
     *
     * @return  void
     */
    private function runMethodsArgumentsSetDefaultValue(MethodArgumentScheme $schemeArgument, \ReflectionParameter $reflectionArgument): void
    {
        if ($reflectionArgument->isVariadic())
        {
            $schemeArgument->isVariadic = true;
        }
        elseif ($reflectionArgument->isOptional())
        {
            $schemeArgument->isValue = true;

            if ($reflectionArgument->isDefaultValueConstant()) $schemeArgument->valueFromConstant = '\\' . $reflectionArgument->getDefaultValueConstantName();
            elseif ($reflectionArgument->isDefaultValueAvailable()) $schemeArgument->value = $reflectionArgument->getDefaultValue();
        }
    }
}
