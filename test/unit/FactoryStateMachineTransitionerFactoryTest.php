<?php

namespace RebelCode\Bookings\FuncTest;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Bookings\FactoryStateMachineTransitionerFactory as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see \RebelCode\Bookings\FactoryStateMachineTransitionerFactory}.
 *
 * @since [*next-version*]
 */
class FactoryStateMachineTransitionerFactoryTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Bookings\FactoryStateMachineTransitionerFactory';

    /**
     * Creates a mock booking factory instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject
     */
    public function createBookingFactory()
    {
        return $this->getMockForAbstractClass('RebelCode\Bookings\BookingFactoryInterface');
    }

    /**
     * Creates a mock state machine instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject
     */
    public function createStateMachine()
    {
        return $this->getMockForAbstractClass('Dhii\State\ReadableStateMachineInterface');
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = new TestSubject();

        $this->assertInstanceOf(
            'RebelCode\Bookings\TransitionerFactoryInterface',
            $subject,
            'Test subject does not implement expected interface.'
        );
    }

    public function testMake()
    {
        $subject = new TestSubject();

        $stateMachine = $this->createStateMachine();
        $bookingFactory = $this->createBookingFactory();

        $config = [
            TestSubject::K_CFG_STATE_MACHINE   => $stateMachine,
            TestSubject::K_CFG_BOOKING_FACTORY => $bookingFactory,
        ];

        $actual = $subject->make($config);

        $this->assertInstanceOf(
            'RebelCode\Bookings\TransitionerInterface',
            $actual,
            'Created instance does not implement expected interface.'
        );
    }

    public function testMakeMissingStateMachine()
    {
        $subject = new TestSubject();

        $this->setExpectedException('Dhii\Factory\Exception\CouldNotMakeExceptionInterface');

        $config = [
            TestSubject::K_CFG_BOOKING_FACTORY => $this->createBookingFactory(),
        ];
        $subject->make($config);
    }

    public function testMakeMissingBookingFactory()
    {
        $subject = new TestSubject();

        $this->setExpectedException('Dhii\Factory\Exception\CouldNotMakeExceptionInterface');

        $config = [
            TestSubject::K_CFG_BOOKING_FACTORY => $this->createBookingFactory(),
        ];
        $subject->make($config);
    }
}
