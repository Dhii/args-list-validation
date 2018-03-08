<?php

namespace Dhii\Validation\FuncTest;

use Dhii\Validation\GetArgsListErrorsCapableTrait as TestSubject;
use InvalidArgumentException;
use OutOfRangeException;
use ReflectionFunction;
use stdClass;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class GetArgsListErrorsCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\Validation\GetArgsListErrorsCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        is_array($methods) && $methods = $this->mergeValues($methods, [
            '__',
            '_createInvalidArgumentException',
            '_createOutOfRangeException'
        ]);

        $mock = $this->mockTraits([static::TEST_SUBJECT_CLASSNAME, 'Dhii\Validation\GetValueTypeErrorCapableTrait'])
            ->setMethods($methods)
            ->getMock();

        $mock->method('__')
                ->will($this->returnCallback(function ($string, $args = []) {
                    return vsprintf($string, $args);
                }));

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The builder for a mock of an object that extends and implements
     *                     the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a mock that uses traits.
     *
     * This is particularly useful for testing integration between multiple traits.
     *
     * @since [*next-version*]
     *
     * @param string[] $traitNames Names of the traits for the mock to use.
     *
     * @return MockBuilder The builder for a mock of an object that uses the traits.
     */
    public function mockTraits($traitNames = [])
    {
        $paddingClassName = uniqid('Traits');
        $definition = vsprintf('abstract class %1$s {%2$s}', [
            $paddingClassName,
            implode(
                ' ',
                array_map(
                    function ($v) {
                        return vsprintf('use %1$s;', [$v]);
                    },
                    $traitNames)),
        ]);
        var_dump($definition);
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Out of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return OutOfRangeException|MockObject The new exception.
     */
    public function createOutOfRangeException($message = '')
    {
        $mock = $this->getMockBuilder('OutOfRangeException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Creates a new Invalid Argument exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return InvalidArgumentException|MockObject The new exception.
     */
    public function createInvalidArgumentException($message = '')
    {
        $mock = $this->getMockBuilder('InvalidArgumentException')
            ->setConstructorArgs([$message])
            ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests that `_getArgsListErrors()` works as expected when no criteria specified.
     *
     * @since [*next-version*]
     */
    public function testGetArgsListErrorsEmptySpec()
    {
        $args = [];
        $spec = [];

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_getArgsListErrors($args, $spec);
        $this->assertCount(0, $result, 'Wrong validation result');
    }

    /**
     * Tests that `_getArgsListErrors()` works as expected when the args list is valid.
     *
     * @since [*next-version*]
     */
    public function testGetArgsListErrorsNone()
    {
        $closure = function ($arg0, $arg1 = 123, $arg2 = null) {
        };
        $args = [uniqid('arg0'), rand(1, 99), new stdClass()];
        $refl = new ReflectionFunction($closure);
        $spec = $refl->getParameters();

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_getArgsListErrors($args, $spec);
        $this->assertCount(0, $result, 'Wrong validation result');
    }

    /**
     * Tests that `_getArgsListErrors()` works as expected when an argument is missing.
     *
     * @since [*next-version*]
     */
    public function testGetArgsListErrorsMissingArgument()
    {
        $closure = function ($arg0, $arg1, $arg2, $arg3 = null) {
        };
        $args = [uniqid('arg0')];
        $refl = new ReflectionFunction($closure);
        $spec = $refl->getParameters();

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_getArgsListErrors($args, $spec);
        $this->assertCount(2, $result, 'Wrong validation result');
        $this->assertRegExp('!^Argument #1 is required$!', $result[0], 'Wrong error reported');
        $this->assertRegExp('!^Argument #2 is required$!', $result[1], 'Wrong error reported');
    }

    /**
     * Tests that `_getArgsListErrors()` works as expected when the args list contains an extra argument.
     *
     * @since [*next-version*]
     */
    public function testGetArgsListErrorsExtraArgument()
    {
        $closure = function ($arg0) {
        };
        $args = [uniqid('arg0'), uniqid('arg1')];
        $refl = new ReflectionFunction($closure);
        $spec = $refl->getParameters();

        $subject = $this->createInstance();
        $_subject = $this->reflect($subject);

        $result = $_subject->_getArgsListErrors($args, $spec);
        $this->assertCount(0, $result, 'Wrong validation result');
    }
}
