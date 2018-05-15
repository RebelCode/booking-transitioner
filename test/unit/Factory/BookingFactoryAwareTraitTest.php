<?php

namespace RebelCode\Bookings\Factory\FuncTest;

use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Bookings\BookingFactoryInterface;
use RebelCode\Bookings\BookingInterface;
use stdClass;
use Xpmock\TestCase;
use RebelCode\Bookings\Factory\BookingFactoryAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class BookingFactoryAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Bookings\Factory\BookingFactoryAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods(['__', '_createInvalidArgumentException'])
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($msg = '', $code = 0, $prev = null) {
                return new InvalidArgumentException($msg, $code, $prev);
            }
        );

        return $mock;
    }

    /**
     * Creates a mock booking factory instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|null $booking The booking to be returned by the factory's `make()` method.
     *
     * @return BookingFactoryInterface
     */
    public function createBookingFactory($booking = null)
    {
        $mock = $this->mock('RebelCode\Bookings\BookingFactoryInterface')
                     ->make($booking);

        return $mock->new();
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
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the getter and setter methods to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetBookingFactory()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input   = $this->createBookingFactory();

        $reflect->_setBookingFactory($input);

        $this->assertSame($input, $reflect->_getBookingFactory(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with a null value to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetBookingFactoryNull()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input   = null;

        $reflect->_setBookingFactory($input);

        $this->assertNull($reflect->_getBookingFactory(), 'Retrieved value is not null.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetBookingFactoryInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input   = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setBookingFactory($input);
    }
}
