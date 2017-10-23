<?php

namespace RebelCode\Bookings\Exception;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use RebelCode\Bookings\TransitionerAwareTrait;
use RebelCode\Bookings\TransitionerInterface;

/**
 * An exception that is thrown is relation to a booking transitioner.
 *
 * @since [*next-version*]
 */
class TransitionerException extends RootException implements TransitionerExceptionInterface
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
     */
    public function __construct(
        $message = null,
        $code = null,
        $previous = null,
        TransitionerInterface $transitioner = null
    ) {
        parent::__construct((string) $message, (int) $code, $previous);

        $this->_setTransitioner($transitioner);
    }
}
