<?php

declare (strict_types=1);
/*
 * This file is part of sebastian/type.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210804\SebastianBergmann\Type;

use function assert;
use function class_exists;
use function count;
use function explode;
use function function_exists;
use function is_array;
use function is_object;
use function is_string;
use function strpos;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
final class CallableType extends \ECSPrefix20210804\SebastianBergmann\Type\Type
{
    /**
     * @var bool
     */
    private $allowsNull;
    public function __construct(bool $nullable)
    {
        $this->allowsNull = $nullable;
    }
    /**
     * @throws RuntimeException
     * @param \SebastianBergmann\Type\Type $other
     */
    public function isAssignable($other) : bool
    {
        if ($this->allowsNull && $other instanceof \ECSPrefix20210804\SebastianBergmann\Type\NullType) {
            return \true;
        }
        if ($other instanceof self) {
            return \true;
        }
        if ($other instanceof \ECSPrefix20210804\SebastianBergmann\Type\ObjectType) {
            if ($this->isClosure($other)) {
                return \true;
            }
            if ($this->hasInvokeMethod($other)) {
                return \true;
            }
        }
        if ($other instanceof \ECSPrefix20210804\SebastianBergmann\Type\SimpleType) {
            if ($this->isFunction($other)) {
                return \true;
            }
            if ($this->isClassCallback($other)) {
                return \true;
            }
            if ($this->isObjectCallback($other)) {
                return \true;
            }
        }
        return \false;
    }
    public function name() : string
    {
        return 'callable';
    }
    public function allowsNull() : bool
    {
        return $this->allowsNull;
    }
    private function isClosure(\ECSPrefix20210804\SebastianBergmann\Type\ObjectType $type) : bool
    {
        return !$type->className()->isNamespaced() && $type->className()->simpleName() === \Closure::class;
    }
    /**
     * @throws RuntimeException
     */
    private function hasInvokeMethod(\ECSPrefix20210804\SebastianBergmann\Type\ObjectType $type) : bool
    {
        $className = $type->className()->qualifiedName();
        \assert(\class_exists($className));
        try {
            $class = new \ReflectionClass($className);
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new \ECSPrefix20210804\SebastianBergmann\Type\RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
            // @codeCoverageIgnoreEnd
        }
        if ($class->hasMethod('__invoke')) {
            return \true;
        }
        return \false;
    }
    private function isFunction(\ECSPrefix20210804\SebastianBergmann\Type\SimpleType $type) : bool
    {
        if (!\is_string($type->value())) {
            return \false;
        }
        return \function_exists($type->value());
    }
    private function isObjectCallback(\ECSPrefix20210804\SebastianBergmann\Type\SimpleType $type) : bool
    {
        if (!\is_array($type->value())) {
            return \false;
        }
        if (\count($type->value()) !== 2) {
            return \false;
        }
        if (!\is_object($type->value()[0]) || !\is_string($type->value()[1])) {
            return \false;
        }
        list($object, $methodName) = $type->value();
        return (new \ReflectionObject($object))->hasMethod($methodName);
    }
    private function isClassCallback(\ECSPrefix20210804\SebastianBergmann\Type\SimpleType $type) : bool
    {
        if (!\is_string($type->value()) && !\is_array($type->value())) {
            return \false;
        }
        if (\is_string($type->value())) {
            if (\strpos($type->value(), '::') === \false) {
                return \false;
            }
            list($className, $methodName) = \explode('::', $type->value());
        }
        if (\is_array($type->value())) {
            if (\count($type->value()) !== 2) {
                return \false;
            }
            if (!\is_string($type->value()[0]) || !\is_string($type->value()[1])) {
                return \false;
            }
            list($className, $methodName) = $type->value();
        }
        \assert(isset($className) && \is_string($className) && \class_exists($className));
        \assert(isset($methodName) && \is_string($methodName));
        try {
            $class = new \ReflectionClass($className);
            if ($class->hasMethod($methodName)) {
                $method = $class->getMethod($methodName);
                return $method->isPublic() && $method->isStatic();
            }
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $e) {
            throw new \ECSPrefix20210804\SebastianBergmann\Type\RuntimeException($e->getMessage(), (int) $e->getCode(), $e);
            // @codeCoverageIgnoreEnd
        }
        return \false;
    }
}