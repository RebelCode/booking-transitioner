<?php

namespace RebelCode\Bookings;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\AbstractBaseCallbackFactory;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;

/**
 * A factory implementation that can create {@see FactoryStateMachineTransitioner} instances.
 *
 * @since [*next-version*]
 */
class FactoryStateMachineTransitionerFactory extends AbstractBaseCallbackFactory implements TransitionerFactoryInterface
{
    /*
     * Provides functionality for reading from any type of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides key normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating not found exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides functionality for creating invalid-argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating out-of-range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides string translation functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The key in the config for the state machine.
     *
     * @since [*next-version*]
     */
    const K_CFG_STATE_MACHINE = 'state-machine';

    /**
     * The key in the config for the booking factory.
     *
     * @since [*next-version*]
     */
    const K_CFG_BOOKING_FACTORY = 'booking-factory';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        return parent::make($config);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getFactoryCallback($config = null)
    {
        return function () use ($config) {
            $stateMachine = $this->_containerGet($config, static::K_CFG_STATE_MACHINE);
            $bookingFactory = $this->_containerGet($config, static::K_CFG_BOOKING_FACTORY);

            return new FactoryStateMachineTransitioner($stateMachine, $bookingFactory);
        };
    }
}
