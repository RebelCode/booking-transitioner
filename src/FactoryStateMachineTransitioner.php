<?php

namespace RebelCode\Bookings;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\State\StateMachineAwareTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use RebelCode\Bookings\Exception\CouldNotTransitionException;
use RebelCode\Bookings\Exception\CouldNotTransitionExceptionInterface;
use RebelCode\Bookings\Exception\TransitionerException;
use RebelCode\Bookings\Exception\TransitionerExceptionInterface;
use RebelCode\Bookings\Factory\BookingFactoryAwareTrait;

/**
 * Concrete implementation of a booking transitioner that uses a state machine for status updates.
 *
 * This implementation uses a booking factory to create new booking instances on transition.
 * The transitions are given as-is to the state machine and the resulting states are used as-is for bookings statuses.
 *
 * @since [*next-version*]
 */
class FactoryStateMachineTransitioner extends AbstractFactoryStateMachineTransitioner implements TransitionerInterface
{
    /*
     * Provides awareness of a state machine.
     *
     * @since [*next-version*]
     */
    use StateMachineAwareTrait {
        _getStateMachine as _getStateMachineInstance;
        _setStateMachine as _setStateMachineInstance;
    }

    /*
     * Provides awareness of a booking factory.
     *
     * @since [*next-version*]
     */
    use BookingFactoryAwareTrait {
        _getBookingFactory as _getBookingFactoryInstance;
        _setBookingFactory as _setBookingFactoryInstance;
    }

    /*
     * Provides functionality for creating exceptions for invalid arguments.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides string i18n capabilities.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ReadableStateMachineInterface $machine        The state machine.
     * @param BookingFactoryInterface|null  $bookingFactory The factory callable for creating bookings.
     */
    public function __construct(ReadableStateMachineInterface $machine, BookingFactoryInterface $bookingFactory = null)
    {
        $this->_setStateMachine($machine);
        $this->_setBookingFactoryInstance($bookingFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transition(BookingInterface $booking, $transition)
    {
        return $this->_transition($booking, $transition);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _normalizeTransition(BookingInterface $booking, $transition)
    {
        return $transition;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getStateMachine(BookingInterface $booking, $transition)
    {
        return $this->_getStateMachineInstance();
    }

    /**
     * Sets the state machine for this instance.
     *
     * @since [*next-version*]
     *
     * @param ReadableStateMachineInterface|null $stateMachine The state machine instance or null
     */
    protected function _setStateMachine($stateMachine)
    {
        if ($stateMachine !== null && !($stateMachine instanceof ReadableStateMachineInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a readable state machine instance'),
                null,
                null,
                $stateMachine
            );
        }

        $this->_setStateMachineInstance($stateMachine);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getBookingFactory(
        BookingInterface $booking,
        $transition,
        ReadableStateMachineInterface $stateMachine
    ) {
        return $this->_getBookingFactoryInstance();
    }

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
    protected function _createTransitionerException(
        $message = null,
        $code = null,
        RootException $previous = null
    ) {
        return new TransitionerException($message, $code, $previous, $this);
    }

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
    protected function _createCouldNotTransitionException(
        $message = null,
        $code = null,
        RootException $previous = null,
        BookingInterface $booking = null,
        $transition = null
    ) {
        return new CouldNotTransitionException($message, $code, $previous, $this, $booking, $transition);
    }
}
