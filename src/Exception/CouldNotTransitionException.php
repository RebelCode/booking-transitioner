<?php

namespace RebelCode\Bookings\Exception;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\TransitionAwareTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use RebelCode\Bookings\BookingAwareTrait;
use RebelCode\Bookings\BookingInterface;
use RebelCode\Bookings\TransitionerAwareTrait;
use RebelCode\Bookings\TransitionerInterface;

/**
 * An exception thrown when a booking transitioner fails to transition a booking.
 *
 * @since [*next-version*]
 */
class CouldNotTransitionException extends RootException implements CouldNotTransitionExceptionInterface
{
    /*
     * Provides awareness of a booking transitioner.
     *
     * @since [*next-version*]
     */
    use TransitionerAwareTrait {
        _getTransitioner as public getTransitioner;
    }

    /*
     * Provides awareness of a transition.
     *
     * @since [*next-version*]
     */
    use TransitionAwareTrait {
        _getTransition as public getTransition;
    }

    /*
     * Provides awareness of a booking.
     *
     * @since [*next-version*]
     */
    use BookingAwareTrait {
        _getBooking as public getBooking;
    }

    /*
     * Adds internal i18n capabilities.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /*
     * Adds internal factory for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null     $message      The error message, if any.
     * @param int|null                   $code         The error code, if any.
     * @param RootException|null         $previous     The previous exception for chaining, if any.
     * @param TransitionerInterface|null $transitioner The transitioner, if any.
     * @param BookingInterface|null      $booking      The booking, if any.
     * @param string|Stringable|null     $transition   The transition, if any.
     */
    public function __construct(
        $message = null,
        $code = null,
        $previous = null,
        TransitionerInterface $transitioner = null,
        BookingInterface $booking = null,
        $transition = null
    ) {
        parent::__construct((string) $message, (int) $code, $previous);

        $this->_setTransitioner($transitioner);
        $this->_setBooking($booking);
        $this->_setTransition($transition);
    }
}
