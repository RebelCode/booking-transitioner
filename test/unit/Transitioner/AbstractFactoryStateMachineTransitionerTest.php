<?php

namespace RebelCode\Bookings\Transitioner\UnitTest;

use Dhii\State\ReadableStateMachineInterface;
use Exception;
use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Bookings\BookingInterface;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AbstractFactoryStateMachineTransitionerTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Bookings\AbstractFactoryStateMachineTransitioner';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock, as an array of method names.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance($methods = [])
    {
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods(
                         array_merge(
                             [
                                 '_getStateMachine',
                                 '_createTransitionerException',
                                 '_createCouldNotTransitionException',
                                 '__',
                             ],
                             $methods
                         )
                     )
                     ->getMockForAbstractClass();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createTransitionerException')->willReturnCallback(
            function($message = '', $code = 0, $prev = null) {
                return new Exception($message, $code, $prev);
            }
        );
        $mock->method('_createCouldNotTransitionException')->willReturn(
            function($message = '', $code = 0, $prev = null) {
                return new Exception($message, $code, $prev);
            }
        );

        return $mock;
    }

    /**
     * Creates a mock booking instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @return BookingInterface
     */
    public function createBooking()
    {
        $mock = $this->mock('RebelCode\Bookings\BookingInterface')
                     ->getStart()
                     ->getEnd()
                     ->getDuration()
                     ->getStatus();

        return $mock->new();
    }

    /**
     * Creates a mock readable state machine instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @return ReadableStateMachineInterface
     */
    public function createReadableStateMachine()
    {
        $mock = $this->mock('Dhii\State\ReadableStateMachineInterface')
                     ->transition()
                     ->canTransition()
                     ->getState()
                     ->getState();

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

        $this->assertInstanceOf(
            static::TEST_SUBJECT_CLASSNAME,
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the factory args getter method.
     *
     * @since [*next-version*]
     */
    public function testGetFactoryArgs()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $booking    = $this->createBooking();
        $transition = uniqid('transition-');
        $machine    = $this->createReadableStateMachine();

        $args     = $reflect->_getBookingFactoryArgs($booking, $transition, $machine);
        $expected = [$booking, $transition, $machine];

        $this->assertEquals($expected, $args, 'Expected and retrieved args do not match');
    }

    /**
     * Tests the booking getter method to ensure that the booking factory is invoked and the created booking is
     * returned.
     *
     * @since [*next-version*]
     */
    public function testGetBooking()
    {
        $subject = $this->createInstance(['_getBookingFactory', '_getBookingFactoryArgs']);
        $reflect = $this->reflect($subject);

        $inBooking  = $this->createBooking();
        $outBooking = $this->createBooking();
        $transition = uniqid('transition-');
        $machine    = $this->createReadableStateMachine();

        $subject->expects($this->once())
                ->method('_getBookingFactoryArgs')
                ->with($inBooking, $transition, $machine)
                ->willReturn([]);

        $factory = function() use ($outBooking) {
            return $outBooking;
        };

        $subject->expects($this->once())
                ->method('_getBookingFactory')
                ->with($inBooking, $transition, $machine)
                ->willReturn($factory);

        $this->assertSame(
            $outBooking,
            $reflect->_getBooking($inBooking, $transition, $machine),
            'Expected and retrieved bookings are not the same.'
        );
    }
}
