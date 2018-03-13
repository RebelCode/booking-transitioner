<?php

namespace RebelCode\Bookings;

use ArrayAccess;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * An abstract implementation of a booking transitioner that uses a state machine for transitions and a factory for
 * creating new bookings.
 *
 * @since [*next-version*]
 */
abstract class AbstractFactoryStateMachineTransitioner extends AbstractStateMachineTransitioner
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getBooking(
        BookingInterface $booking,
        $transition,
        ReadableStateMachineInterface $stateMachine
    ) {
        $factory    = $this->_getBookingFactory($booking, $transition, $stateMachine);
        $args       = $this->_getBookingFactoryArgs($booking, $transition, $stateMachine);
        $newBooking = $factory->make($args);

        return $newBooking;
    }

    /**
     * Retrieves the arguments to pass to the booking factory.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface              $booking      The booking.
     * @param string|Stringable|null        $transition   The transition.
     * @param ReadableStateMachineInterface $stateMachine The state machine.
     *
     * @return array|ArrayAccess|stdClass|ContainerInterface
     */
    protected function _getBookingFactoryArgs(
        BookingInterface $booking,
        $transition,
        ReadableStateMachineInterface $stateMachine
    ) {
        return [
            'start'    => $booking->getStart(),
            'end'      => $booking->getEnd(),
            'duration' => $booking->getDuration(),
            'status'   => $stateMachine->getState(),
        ];
    }

    /**
     * Retrieves the booking factory to use to create the new booking instance.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface              $booking      The booking.
     * @param string|Stringable|null        $transition   The transition.
     * @param ReadableStateMachineInterface $stateMachine The state machine.
     *
     * @return BookingFactoryInterface The booking factory.
     */
    abstract protected function _getBookingFactory(
        BookingInterface $booking,
        $transition,
        ReadableStateMachineInterface $stateMachine
    );
}
