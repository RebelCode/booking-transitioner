<?php

namespace RebelCode\Bookings\Factory;

use Exception as RootException;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use RebelCode\Bookings\BookingFactoryInterface;

/**
 * Common functionality for objects that are aware of a booking factory.
 *
 * @since [*next-version*]
 */
trait BookingFactoryAwareTrait
{
    /**
     * The booking factory.
     *
     * @since [*next-version*]
     *
     * @var BookingFactoryInterface|null
     */
    protected $bookingFactory;

    /**
     * Retrieves the booking factory associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return BookingFactoryInterface|null The booking factory instance, if any.
     */
    protected function _getBookingFactory()
    {
        return $this->bookingFactory;
    }

    /**
     * Sets the booking factory for this instance.
     *
     * @since [*next-version*]
     *
     * @param BookingFactoryInterface|null $bookingFactory The booking factory instance, or null.
     */
    protected function _setBookingFactory($bookingFactory)
    {
        if ($bookingFactory !== null && !($bookingFactory instanceof BookingFactoryInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a valid booking factory'),
                null,
                null,
                $bookingFactory
            );
        }

        $this->bookingFactory = $bookingFactory;
    }

    /**
     * Creates a new invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
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
