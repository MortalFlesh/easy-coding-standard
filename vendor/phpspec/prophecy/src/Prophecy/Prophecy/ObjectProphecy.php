<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix20210804\Prophecy\Prophecy;

use ECSPrefix20210804\SebastianBergmann\Comparator\ComparisonFailure;
use ECSPrefix20210804\Prophecy\Comparator\Factory as ComparatorFactory;
use ECSPrefix20210804\Prophecy\Call\Call;
use ECSPrefix20210804\Prophecy\Doubler\LazyDouble;
use ECSPrefix20210804\Prophecy\Argument\ArgumentsWildcard;
use ECSPrefix20210804\Prophecy\Call\CallCenter;
use ECSPrefix20210804\Prophecy\Exception\Prophecy\ObjectProphecyException;
use ECSPrefix20210804\Prophecy\Exception\Prophecy\MethodProphecyException;
use ECSPrefix20210804\Prophecy\Exception\Prediction\AggregateException;
use ECSPrefix20210804\Prophecy\Exception\Prediction\PredictionException;
/**
 * Object prophecy.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class ObjectProphecy implements \ECSPrefix20210804\Prophecy\Prophecy\ProphecyInterface
{
    private $lazyDouble;
    private $callCenter;
    private $revealer;
    private $comparatorFactory;
    /**
     * @var MethodProphecy[][]
     */
    private $methodProphecies = array();
    /**
     * Initializes object prophecy.
     *
     * @param LazyDouble        $lazyDouble
     * @param CallCenter        $callCenter
     * @param RevealerInterface $revealer
     * @param ComparatorFactory $comparatorFactory
     */
    public function __construct(\ECSPrefix20210804\Prophecy\Doubler\LazyDouble $lazyDouble, \ECSPrefix20210804\Prophecy\Call\CallCenter $callCenter = null, \ECSPrefix20210804\Prophecy\Prophecy\RevealerInterface $revealer = null, \ECSPrefix20210804\Prophecy\Comparator\Factory $comparatorFactory = null)
    {
        $this->lazyDouble = $lazyDouble;
        $this->callCenter = $callCenter ?: new \ECSPrefix20210804\Prophecy\Call\CallCenter();
        $this->revealer = $revealer ?: new \ECSPrefix20210804\Prophecy\Prophecy\Revealer();
        $this->comparatorFactory = $comparatorFactory ?: \ECSPrefix20210804\Prophecy\Comparator\Factory::getInstance();
    }
    /**
     * Forces double to extend specific class.
     *
     * @param string $class
     *
     * @return $this
     */
    public function willExtend($class)
    {
        $this->lazyDouble->setParentClass($class);
        return $this;
    }
    /**
     * Forces double to implement specific interface.
     *
     * @param string $interface
     *
     * @return $this
     */
    public function willImplement($interface)
    {
        $this->lazyDouble->addInterface($interface);
        return $this;
    }
    /**
     * Sets constructor arguments.
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function willBeConstructedWith(array $arguments = null)
    {
        $this->lazyDouble->setArguments($arguments);
        return $this;
    }
    /**
     * Reveals double.
     *
     * @return object
     *
     * @throws \Prophecy\Exception\Prophecy\ObjectProphecyException If double doesn't implement needed interface
     */
    public function reveal()
    {
        $double = $this->lazyDouble->getInstance();
        if (null === $double || !$double instanceof \ECSPrefix20210804\Prophecy\Prophecy\ProphecySubjectInterface) {
            throw new \ECSPrefix20210804\Prophecy\Exception\Prophecy\ObjectProphecyException("Generated double must implement ProphecySubjectInterface, but it does not.\n" . 'It seems you have wrongly configured doubler without required ClassPatch.', $this);
        }
        $double->setProphecy($this);
        return $double;
    }
    /**
     * Adds method prophecy to object prophecy.
     *
     * @param MethodProphecy $methodProphecy
     *
     * @throws \Prophecy\Exception\Prophecy\MethodProphecyException If method prophecy doesn't
     *                                                              have arguments wildcard
     */
    public function addMethodProphecy(\ECSPrefix20210804\Prophecy\Prophecy\MethodProphecy $methodProphecy)
    {
        $argumentsWildcard = $methodProphecy->getArgumentsWildcard();
        if (null === $argumentsWildcard) {
            throw new \ECSPrefix20210804\Prophecy\Exception\Prophecy\MethodProphecyException(\sprintf("Can not add prophecy for a method `%s::%s()`\n" . "as you did not specify arguments wildcard for it.", \get_class($this->reveal()), $methodProphecy->getMethodName()), $methodProphecy);
        }
        $methodName = \strtolower($methodProphecy->getMethodName());
        if (!isset($this->methodProphecies[$methodName])) {
            $this->methodProphecies[$methodName] = array();
        }
        $this->methodProphecies[$methodName][] = $methodProphecy;
    }
    /**
     * Returns either all or related to single method prophecies.
     *
     * @param null|string $methodName
     *
     * @return MethodProphecy[]
     */
    public function getMethodProphecies($methodName = null)
    {
        if (null === $methodName) {
            return $this->methodProphecies;
        }
        $methodName = \strtolower($methodName);
        if (!isset($this->methodProphecies[$methodName])) {
            return array();
        }
        return $this->methodProphecies[$methodName];
    }
    /**
     * Makes specific method call.
     *
     * @param string $methodName
     * @param array  $arguments
     *
     * @return mixed
     */
    public function makeProphecyMethodCall($methodName, array $arguments)
    {
        $arguments = $this->revealer->reveal($arguments);
        $return = $this->callCenter->makeCall($this, $methodName, $arguments);
        return $this->revealer->reveal($return);
    }
    /**
     * Finds calls by method name & arguments wildcard.
     *
     * @param string            $methodName
     * @param ArgumentsWildcard $wildcard
     *
     * @return Call[]
     */
    public function findProphecyMethodCalls($methodName, \ECSPrefix20210804\Prophecy\Argument\ArgumentsWildcard $wildcard)
    {
        return $this->callCenter->findCalls($methodName, $wildcard);
    }
    /**
     * Checks that registered method predictions do not fail.
     *
     * @throws \Prophecy\Exception\Prediction\AggregateException If any of registered predictions fail
     * @throws \Prophecy\Exception\Call\UnexpectedCallException
     */
    public function checkProphecyMethodsPredictions()
    {
        $exception = new \ECSPrefix20210804\Prophecy\Exception\Prediction\AggregateException(\sprintf("%s:\n", \get_class($this->reveal())));
        $exception->setObjectProphecy($this);
        $this->callCenter->checkUnexpectedCalls();
        foreach ($this->methodProphecies as $prophecies) {
            foreach ($prophecies as $prophecy) {
                try {
                    $prophecy->checkPrediction();
                } catch (\ECSPrefix20210804\Prophecy\Exception\Prediction\PredictionException $e) {
                    $exception->append($e);
                }
            }
        }
        if (\count($exception->getExceptions())) {
            throw $exception;
        }
    }
    /**
     * Creates new method prophecy using specified method name and arguments.
     *
     * @param string $methodName
     * @param array  $arguments
     *
     * @return MethodProphecy
     */
    public function __call($methodName, array $arguments)
    {
        $arguments = new \ECSPrefix20210804\Prophecy\Argument\ArgumentsWildcard($this->revealer->reveal($arguments));
        foreach ($this->getMethodProphecies($methodName) as $prophecy) {
            $argumentsWildcard = $prophecy->getArgumentsWildcard();
            $comparator = $this->comparatorFactory->getComparatorFor($argumentsWildcard, $arguments);
            try {
                $comparator->assertEquals($argumentsWildcard, $arguments);
                return $prophecy;
            } catch (\ECSPrefix20210804\SebastianBergmann\Comparator\ComparisonFailure $failure) {
            }
        }
        return new \ECSPrefix20210804\Prophecy\Prophecy\MethodProphecy($this, $methodName, $arguments);
    }
    /**
     * Tries to get property value from double.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->reveal()->{$name};
    }
    /**
     * Tries to set property value to double.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->reveal()->{$name} = $this->revealer->reveal($value);
    }
}