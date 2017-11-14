<?php

namespace RebelCode\Bookings;

use Dhii\State\ReadableStateMachineInterface;
use Dhii\Util\String\StringableInterface as Stringable;

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
        $newBooking = call_user_func_array($factory, $args);

        return $newBooking;
    }

    /**
     * Retrieves the arguments to pass to the booking factory.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface              $booking
     * @param string|Stringable|null        $transition
     * @param ReadableStateMachineInterface $stateMachine
     *
     * @return array
     */
    protected function _getBookingFactoryArgs(
        BookingInterface $booking,
        $transition,
        ReadableStateMachineInterface $stateMachine
    ) {
        return [$booking, $transition, $stateMachine];
    }

    /**
     * Retrieves the booking factory callable to use to create the new booking instance.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface              $booking      The booking.
     * @param string|Stringable|null        $transition   The transition.
     * @param ReadableStateMachineInterface $stateMachine The state machine.
     *
     * @return callable The booking factory callable. Must return a {@link BookingInterface}.
     */
    abstract protected function _getBookingFactory(
        BookingInterface $booking,
        $transition,
        ReadableStateMachineInterface $stateMachine
    );
}
