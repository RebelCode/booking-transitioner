<?php

namespace RebelCode\Bookings\Transitioner\UnitTest;

use Dhii\State\ReadableStateMachineInterface;
use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Bookings\BookingFactoryInterface;
use RebelCode\Bookings\BookingInterface;
use ReflectionClass;
use Xpmock\TestCase;

/**
 * Tests {@see RebelCode\Bookings\Transitioner\FactoryStateMachineTransitioner}.
 *
 * @since [*next-version*]
 */
class FactoryStateMachineTransitionerTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Bookings\FactoryStateMachineTransitioner';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods($methods)
                        ->disableOriginalConstructor();

        return $builder->getMock();
    }

    /**
     * Creates a readable state machine instance.
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
                     ->getState();

        return $mock->new();
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

        $this->assertInstanceOf(
            'RebelCode\Bookings\TransitionerInterface',
            $subject,
            'Test subject does not implement expected interface.'
        );

        $this->assertInstanceOf(
            'RebelCode\Bookings\AbstractFactoryStateMachineTransitioner',
            $subject,
            'Test subject does not extend expected parent class.'
        );
    }

    /**
     * Tests the constructor to ensure that the appropriate setters are invoked for the given arguments.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        $bkFactory  = $this->createBookingFactory();
        $readableSm = $this->createReadableStateMachine();

        $subject = $this->createInstance(['_setStateMachine', '_setBookingFactoryInstance']);

        $subject->expects($this->once())
                ->method('_setStateMachine')
                ->with($readableSm);

        $subject->expects($this->once())
                ->method('_setBookingFactoryInstance')
                ->with($bkFactory);

        $reflect     = new ReflectionClass($subject);
        $constructor = $reflect->getConstructor();
        $constructor->invoke($subject, $readableSm, $bkFactory);
    }

    /**
     * Tests the transition normalization method to ensure that the transition remains the same.
     *
     * @since [*next-version*]
     */
    public function testNormalizeTransition()
    {
        $subject    = $this->createInstance();
        $reflect    = $this->reflect($subject);
        $booking    = $this->createBooking();
        $transition = uniqid('transition-');

        $this->assertEquals(
            $transition,
            $reflect->_normalizeTransition($booking, $transition),
            'Input and output transitions are not the same.'
        );
    }

    /**
     * Tests the transition method.
     *
     * @since [*next-version*]
     */
    public function testTransition()
    {
        $subject    = $this->createInstance(['_transition']);
        $booking    = $this->createBooking();
        $newBooking = $this->createBooking();
        $transition = uniqid('transition-');

        $subject->expects($this->once())
                ->method('_transition')
                ->with($booking, $transition)
                ->willReturn($newBooking);

        $this->assertSame(
            $newBooking,
            $subject->transition($booking, $transition),
            'Returned booking is not identical to booking from transition result.'
        );
    }

    /**
     * Tests the state machine getter and setter method to assert that the state machine returned for use
     * in the transition algorithm is equivalent to the state machine set through the setter.
     *
     * @since [*next-version*]
     */
    public function testGetSetStateMachine()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $readableSm = $this->createReadableStateMachine();
        $booking    = $this->createBooking();
        $transition = uniqid('transition-');

        $reflect->_setStateMachineInstance($readableSm);

        $this->assertSame(
            $readableSm,
            $reflect->_getStateMachine($booking, $transition),
            'Set and retrieved state machines are not the same.'
        );
    }

    /**
     * Tests the state machine getter and setter method to assert that the state machine returned for use
     * in the transition algorithm is equivalent to the state machine set through the setter.
     *
     * @since [*next-version*]
     */
    public function testGetSetBookingFactory()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $factory    = $this->createBookingFactory();
        $readableSm = $this->createReadableStateMachine();
        $booking    = $this->createBooking();
        $transition = uniqid('transition-');

        $reflect->_setBookingFactoryInstance($factory);

        $this->assertSame(
            $factory,
            $reflect->_getBookingFactory($booking, $transition, $readableSm),
            'Set and retrieved booking factories are not the same.'
        );
    }

    /**
     * Tests the "transitioner exception" factory to assert whether a correct exception instance is created.
     *
     * @since [*next-version*]
     */
    public function testCreateTransitionerException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $message  = uniqid('message-');
        $code     = rand();
        $previous = new \Exception();

        $exception = $reflect->_createTransitionerException($message, $code, $previous);

        $this->assertInstanceOf(
            'RebelCode\Bookings\Exception\TransitionerExceptionInterface',
            $exception,
            'Created exception does not implement expected interface.'
        );

        $this->assertEquals($message, $exception->getMessage(), 'Exception message is incorrect.');
        $this->assertEquals($code, $exception->getCode(), 'Exception code is incorrect.');
        $this->assertSame($previous, $exception->getPrevious(), 'Inner exception instance is incorrect.');
        $this->assertSame($subject, $exception->getTransitioner(), 'Exception transitioner is incorrect.');
    }

    /**
     * Tests the "could not transition exception" factory to assert whether a correct exception instance is created.
     *
     * @since [*next-version*]
     */
    public function testCreateCouldNotTransitionException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $message    = uniqid('message-');
        $code       = rand();
        $previous   = new \Exception();
        $booking    = $this->createBooking();
        $transition = uniqid('transition-');

        $exception = $reflect->_createCouldNotTransitionException($message, $code, $previous, $booking, $transition);

        $this->assertInstanceOf(
            'RebelCode\Bookings\Exception\CouldNotTransitionExceptionInterface',
            $exception,
            'Created exception does not implement expected interface.'
        );

        $this->assertEquals($message, $exception->getMessage(), 'Exception message is incorrect.');
        $this->assertEquals($code, $exception->getCode(), 'Exception code is incorrect.');
        $this->assertSame($previous, $exception->getPrevious(), 'Inner exception instance is incorrect.');
        $this->assertSame($subject, $exception->getTransitioner(), 'Exception transitioner is incorrect.');
        $this->assertSame($booking, $exception->getBooking(), 'Exception booking is incorrect.');
        $this->assertEquals($transition, $exception->getTransition(), 'Exception transition is incorrect.');
    }
}
