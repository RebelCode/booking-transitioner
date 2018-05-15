<?php

namespace RebelCode\Bookings;

use ArrayAccess;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

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
        $factoryArgs = [
            'start'    => $booking->getStart(),
            'end'      => $booking->getEnd(),
            'duration' => $booking->getDuration(),
            'status'   => $stateMachine->getState(),
        ];

        try {
            $booking = $this->_normalizeIterable($booking);

            foreach ($booking as $_key => $_value) {
                if (array_key_exists($_key, $factoryArgs)) {
                    continue;
                }

                $factoryArgs[$_key] = $_value;
            }
        } catch (InvalidArgumentException $invalidArgumentException) {
            // Do nothing
        }

        return $factoryArgs;
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

    /**
     * Normalizes an iterable.
     *
     * Makes sure that the return value can be iterated over.
     *
     * @since [*next-version*]
     *
     * @param mixed $iterable The iterable to normalize.
     *
     * @throws InvalidArgumentException If the iterable could not be normalized.
     *
     * @return array|Traversable|stdClass The normalized iterable.
     */
    abstract protected function _normalizeIterable($iterable);
}
