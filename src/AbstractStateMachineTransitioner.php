<?php

namespace RebelCode\Bookings;

use Dhii\State\Exception\CouldNotTransitionExceptionInterface as SmCouldNotTransitionExceptionInterface;
use Dhii\State\Exception\StateMachineExceptionInterface;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\State\StateMachineInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use RebelCode\Bookings\Exception\CouldNotTransitionExceptionInterface;
use RebelCode\Bookings\Exception\TransitionerExceptionInterface;

/**
 * Abstract functionality for booking transitioners that use a state machine.
 *
 * @since [*next-version*]
 */
abstract class AbstractStateMachineTransitioner
{
    /**
     * Applies a transition to a booking.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface       $booking    The booking to transition.
     * @param string|Stringable|null $transition The transition to apply.
     *
     * @return BookingInterface
     */
    protected function _transition(BookingInterface $booking, $transition)
    {
        try {
            $nTransition  = $this->_normalizeTransition($booking, $transition);
            $stateMachine = $this->_getStateMachine($booking, $nTransition);

            if ($stateMachine === null) {
                throw $this->_createTransitionerException($this->__('State machine is null'), null, null);
            }

            $rStateMachine = $this->_doStateMachineTransition($booking, $nTransition, $stateMachine);
            $newBooking    = $this->_getBooking($booking, $nTransition, $rStateMachine);

            return $newBooking;
        } catch (StateMachineExceptionInterface $smException) {
            throw $this->_createTransitionerException(
                $this->__('An error occurred during transition'),
                null,
                $smException
            );
        } catch (SmCouldNotTransitionExceptionInterface $smtException) {
            throw $this->_createCouldNotTransitionException(
                $this->__('Failed to transition booking'),
                null,
                $smtException,
                $booking
            );
        }
    }

    /**
     * Applies a transition to a state machine.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|null  $booking      The booking related to this transition.
     * @param string|Stringable|null $transition   The transition to submit to the state machine.
     * @param StateMachineInterface  $stateMachine The state machine.
     *
     * @return ReadableStateMachineInterface The resulting state machine.
     *
     * @throws StateMachineExceptionInterface If the state machine encountered an error.
     * @throws SmCouldNotTransitionExceptionInterface If the state machine could not transition.
     * @throws TransitionerExceptionInterface If the resulting state machine is not readable.
     */
    protected function _doStateMachineTransition(
        BookingInterface $booking,
        $transition,
        StateMachineInterface $stateMachine
    ) {
        $stateMachine = $stateMachine->transition($transition);

        if ($stateMachine instanceof ReadableStateMachineInterface) {
            return $stateMachine;
        }

        throw $this->_createTransitionerException(
            $this->__('State machine is not readable after transition'),
            null,
            null
        );
    }

    /**
     * Normalizes a transition before passing it on to the state machine.
     *
     * By default, the state machine transition is equivalent to the given booking transition.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface       $booking    The booking that is about to be transitioned.
     * @param string|Stringable|null $transition The transition to normalize.
     *
     * @return string|Stringable|null The normalized transition.
     */
    abstract protected function _normalizeTransition(BookingInterface $booking, $transition);

    /**
     * Retrieves the state machine associated with this instance.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface       $booking    The booking.
     * @param string|Stringable|null $transition The transition.
     *
     * @return StateMachineInterface|null The state machine.
     */
    abstract protected function _getStateMachine(BookingInterface $booking, $transition);

    /**
     * Retrieves the booking instance with the new status.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface              $booking      The booking with the old status.
     * @param string|Stringable|null        $transition   The transition that was applied.
     * @param ReadableStateMachineInterface $stateMachine The transitioned state machine.
     *
     * @return BookingInterface The booking instance.
     */
    abstract protected function _getBooking(
        BookingInterface $booking,
        $transition,
        ReadableStateMachineInterface $stateMachine
    );

    /**
     * Creates a transitioner exception instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The previous exception for chaining, if any.
     *
     * @return TransitionerExceptionInterface The created exception instance.
     */
    abstract protected function _createTransitionerException(
        $message = null,
        $code = null,
        RootException $previous = null
    );

    /**
     * Creates an exception instance for failed transitions.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message    The error message, if any.
     * @param int|null               $code       The error code, if any.
     * @param RootException|null     $previous   The previous exception for chaining, if any.
     * @param BookingInterface|null  $booking    The booking, if any.
     * @param string|Stringable|null $transition The transition, if any.
     *
     * @return CouldNotTransitionExceptionInterface The created exception instance.
     */
    abstract protected function _createCouldNotTransitionException(
        $message = null,
        $code = null,
        RootException $previous = null,
        BookingInterface $booking = null,
        $transition = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
