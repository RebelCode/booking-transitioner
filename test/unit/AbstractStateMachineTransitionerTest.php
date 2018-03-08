<?php

namespace RebelCode\Bookings\Transitioner\UnitTest;

use Exception;
use PHPUnit_Framework_MockObject_MockBuilder;
use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Bookings\BookingInterface;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AbstractStateMachineTransitionerTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Bookings\AbstractStateMachineTransitioner';

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
                                 '_normalizeTransition',
                                 '_getStateMachine',
                                 '_getBooking',
                                 '_createTransitionerException',
                                 '_createCouldNotTransitionException',
                                 '__',
                             ],
                             $methods
                         )
                     )
                     ->getMockForAbstractClass();

        $mock->method('_normalizeTransition')->willReturnArgument(1);
        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createTransitionerException')->willReturnCallback(
            function($message = '', $code = 0, $prev = null) {
                return new Exception($message, $code, $prev);
            }
        );
        $mock->method('_createCouldNotTransitionException')->willReturnCallback(
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
     * Creates a mock state machine instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createStateMachine()
    {
        $mock = $this->getMockBuilder('Dhii\State\StateMachineInterface')
                     ->setMethods(['transition', 'canTransition'])
                     ->getMockForAbstractClass();

        return $mock;
    }

    /**
     * Creates a mock state machine instance for testing purposes.
     *
     * @since [*next-version*]
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createReadableStateMachine()
    {
        $mock = $this->getMockBuilder('Dhii\State\ReadableStateMachineInterface')
                     ->setMethods(['transition', 'canTransition', 'getState'])
                     ->getMockForAbstractClass();

        return $mock;
    }

    /**
     * Creates an exception mock for an exception interface.
     *
     * @since [*next-version*]
     *
     * @param string $name      The name of the exception mock class.
     * @param string $interface The name of the interface.
     *
     * @return PHPUnit_Framework_MockObject_MockBuilder
     */
    public function createExceptionInterfaceMock($name, $interface)
    {
        eval(sprintf('abstract class %1$s extends Exception implements %2$s {}', $name, $interface));

        return $this->getMockBuilder($name);
    }

    /**
     * Creates a state machine exception.
     *
     * @since [*next-version*]
     *
     * @param string         $msg
     * @param int            $code
     * @param Exception|null $prev
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createStateMachineException($msg = '', $code = 0, Exception $prev = null)
    {
        $mockBuilder = $this->createExceptionInterfaceMock(
            'StateMachineException',
            'Dhii\State\Exception\StateMachineExceptionInterface'
        );

        $mockBuilder->setMethods(['getStateMachine']);
        $mockBuilder->setConstructorArgs([$msg, $code, $prev]);

        return $mockBuilder->getMockForAbstractClass();
    }

    /**
     * Creates a state machine exception.
     *
     * @since [*next-version*]
     *
     * @param string         $msg
     * @param int            $code
     * @param Exception|null $prev
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createCouldNotTransitionException($msg = '', $code = 0, Exception $prev = null)
    {
        $mockBuilder = $this->createExceptionInterfaceMock(
            'CouldNotTransitionException',
            'Dhii\State\Exception\CouldNotTransitionExceptionInterface'
        );

        $mockBuilder->setMethods(['getStateMachine', 'getTransition']);
        $mockBuilder->setConstructorArgs([$msg, $code, $prev]);

        return $mockBuilder->getMock();
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
     * Tests the state machine transitioner method to ensure that the returned state machine is a result of the
     * state machine's transition.
     *
     * @since [*next-version*]
     */
    public function testDoStateMachineTransition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $booking       = $this->createBooking();
        $transition    = uniqid('transition-');
        $rStateMachine = $this->createReadableStateMachine();

        // Mock state machine to return the above mocked readable state machine
        $stateMachine = $this->createStateMachine();
        $stateMachine->expects($this->once())
                     ->method('transition')
                     ->with($transition)
                     ->willReturn($rStateMachine);

        $result = $reflect->_doStateMachineTransition($booking, $transition, $stateMachine);

        $this->assertSame($rStateMachine, $result, 'Expected and retrieved state machines are not the same.');
    }

    /**
     * Tests the state machine transitioner method to ensure that an exception is thrown if the resulting state machine
     * is not readable.
     *
     * @since [*next-version*]
     */
    public function testDoStateMachineTransitionNotReadable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $booking        = $this->createBooking();
        $transition     = uniqid('transition-');
        $nrStateMachine = $this->createStateMachine();

        // Mock the state machine to return the above mocked non-readable state machine
        $stateMachine = $this->createStateMachine();
        $stateMachine->expects($this->once())
                     ->method('transition')
                     ->with($transition)
                     ->willReturn($nrStateMachine);

        // Mock exception factory method
        $subject->expects($this->once())
                ->method('_createTransitionerException')
                ->willReturn(new Exception());

        $this->setExpectedException('Exception');

        $reflect->_doStateMachineTransition($booking, $transition, $stateMachine);
    }

    /**
     * Tests the transition method to ensure that the correct booking is returned on success.
     *
     * @since [*next-version*]
     */
    public function testTransition()
    {
        $subject = $this->createInstance(['_doStateMachineTransition']);
        $reflect = $this->reflect($subject);

        $transition    = uniqid('transition-');
        $booking       = $this->createBooking();
        $newBooking    = $this->createBooking();
        $stateMachine  = $this->createStateMachine();
        $rStateMachine = $this->createReadableStateMachine();

        // Mock state machine getter to return a state machine
        // when given the argument booking and normalized transition.
        $subject->expects($this->once())
                ->method('_getStateMachine')
                ->with($booking, $transition)
                ->willReturn($stateMachine);

        // Mock and expect the state machine transition method to return a readable state machine
        // when given the argument booking, normalized transition and state machine.
        $subject->expects($this->once())
                ->method('_doStateMachineTransition')
                ->with($booking, $transition, $stateMachine)
                ->willReturn($rStateMachine);

        // Mock and expect the booking getter to return a new booking
        // when given the argument booking, normalized transition and readable state machine
        $subject->expects($this->once())
                ->method('_getBooking')
                ->with($booking, $transition, $rStateMachine)
                ->willReturn($newBooking);

        $result = $reflect->_transition($booking, $transition);

        $this->assertSame($newBooking, $result, 'Expected and retrieved booking are not the same.');
    }

    /**
     * Tests the transition method with a null state machine retrieved internally to test error handling.
     *
     * @since [*next-version*]
     */
    public function testTransitionNullStateMachine()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $transition = uniqid('transition-');
        $booking    = $this->createBooking();

        // Mock and expect the state machine getter to return a state machine
        // when given the argument booking and normalized transition.
        $subject->expects($this->once())
                ->method('_getStateMachine')
                ->with($booking, $transition)
                ->willReturn(null);

        $this->setExpectedException('Exception');

        $reflect->_transition($booking, $transition);
    }

    /**
     * Tests the transition method when a state-machine exception is thrown is test error handling.
     *
     * @since [*next-version*]
     */
    public function testTransitionStateMachineException()
    {
        $subject = $this->createInstance(['_doStateMachineTransition']);
        $reflect = $this->reflect($subject);

        $transition   = uniqid('transition-');
        $booking      = $this->createBooking();
        $stateMachine = $this->createStateMachine();
        $smException  = $this->createStateMachineException();

        // Mock and expect the state machine getter to return a state machine
        // when given the argument booking and normalized transition.
        $subject->expects($this->once())
                ->method('_getStateMachine')
                ->with($booking, $transition)
                ->willReturn($stateMachine);

        // Mock and expect the state machine transition method to throw a state-machine exception
        $subject->expects($this->once())
                ->method('_doStateMachineTransition')
                ->with($booking, $transition, $stateMachine)
                ->willThrowException($smException);

        try {
            $reflect->_transition($booking, $transition);

            $this->fail('Expected exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertSame(
                $smException,
                $e->getPrevious(),
                'Previous exception is not the state machine exception.'
            );
        }
    }

    /**
     * Tests the transition method when a could-not-transition exception is thrown is test error handling.
     *
     * @since [*next-version*]
     */
    public function testTransitionCouldNotTransitionException()
    {
        $subject = $this->createInstance(['_doStateMachineTransition']);
        $reflect = $this->reflect($subject);

        $transition   = uniqid('transition-');
        $booking      = $this->createBooking();
        $stateMachine = $this->createStateMachine();
        $cntException = $this->createCouldNotTransitionException();

        // Mock state machine getter to return a state machine
        // when given the argument booking and normalized transition.
        $subject->expects($this->once())
                ->method('_getStateMachine')
                ->with($booking, $transition)
                ->willReturn($stateMachine);

        // Mock and expect the state machine transition method to throw a could-not-transition exception
        $subject->expects($this->once())
                ->method('_doStateMachineTransition')
                ->with($booking, $transition, $stateMachine)
                ->willThrowException($cntException);

        try {
            $reflect->_transition($booking, $transition);

            $this->fail('Expected exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertSame(
                $cntException,
                $e->getPrevious(),
                'Previous exception is not the state machine exception.'
            );
        }
    }
}
