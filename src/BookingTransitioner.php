<?php

namespace RebelCode\Bookings;

use ArrayAccess;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\CreateCouldNotTransitionExceptionCapableTrait;
use Dhii\Data\CreateTransitionerExceptionCapableTrait;
use Dhii\Data\StateAwareFactoryInterface;
use Dhii\Data\StateAwareInterface;
use Dhii\Data\TransitionCapableStateMachineTrait;
use Dhii\Data\TransitionerInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\State\StateMachineFactoryInterface;
use Exception as RootException;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * Implementation of a booking transitioner, that uses a state machine to determine the new booking status.
 *
 * @since [*next-version*]
 */
class BookingTransitioner implements TransitionerInterface
{
    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use TransitionCapableStateMachineTrait;

    /* @since [*next-version*] */
    use CreateTransitionerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotTransitionExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The state-aware factory instance.
     *
     * @since [*next-version*]
     *
     * @var StateAwareFactoryInterface
     */
    protected $stateAwareFactory;

    /**
     * The state machine factory instance.
     *
     * @since [*next-version*]
     *
     * @var StateMachineFactoryInterface
     */
    protected $stateMachineFactory;

    /**
     * A container of state keys to maps of transitions to destination states.
     *
     * @since [*next-version*]
     *
     * @var array|ArrayAccess|stdClass|ContainerInterface
     */
    protected $transitions;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $transitions         A container of state keys to maps of
     *                                                                           transitions to destination states.
     * @param StateMachineFactoryInterface                  $stateMachineFactory The state machine factory instance.
     * @param StateAwareFactoryInterface                    $stateAwareFactory   The state-aware factory instance.
     */
    public function __construct(
        $transitions,
        StateMachineFactoryInterface $stateMachineFactory,
        StateAwareFactoryInterface $stateAwareFactory
    ) {
        $this->_setTransitions($transitions);
        $this->_setStateMachineFactory($stateMachineFactory);
        $this->_setStateAwareFactory($stateAwareFactory);
    }

    /**
     * Retrieves the state-aware factory instance.
     *
     * @since [*next-version*]
     *
     * @return StateAwareFactoryInterface The state-aware factory instance.
     */
    protected function _getStateAwareFactory()
    {
        return $this->stateAwareFactory;
    }

    /**
     * Sets the state-aware factory instance.
     *
     * @since [*next-version*]
     *
     * @param StateAwareFactoryInterface $stateAwareFactory The state-aware factory instance.
     */
    protected function _setStateAwareFactory(StateAwareFactoryInterface $stateAwareFactory)
    {
        $this->stateAwareFactory = $stateAwareFactory;
    }

    /**
     * Retrieves the state machine factory.
     *
     * @since [*next-version*]
     *
     * @return StateMachineFactoryInterface The state machine factory instance.
     */
    protected function _getStateMachineFactory()
    {
        return $this->stateMachineFactory;
    }

    /**
     * Sets the state machine factory.
     *
     * @since [*next-version*]
     *
     * @param StateMachineFactoryInterface $stateMachineFactory The state machine factory instance.
     */
    protected function _setStateMachineFactory($stateMachineFactory)
    {
        $this->stateMachineFactory = $stateMachineFactory;
    }

    /**
     * Retrieves the transitions to use for the created state machines.
     *
     * @since [*next-version*]
     *
     * @return array|stdClass|ArrayAccess|ContainerInterface A container of state keys to maps of transitions to
     *                                                       destination states.
     */
    protected function _getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Sets the transitions to use for the created state machines.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $transitions A container of state keys to maps of
     *                                                                   transitions to destination states.
     */
    protected function _setTransitions($transitions)
    {
        $this->transitions = $this->_normalizeContainer($transitions);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transition(StateAwareInterface $subject, $transition)
    {
        return $this->_transition($subject, $transition);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _normalizeTransition(StateAwareInterface $subject, $transition)
    {
        return $transition;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getStateMachineForTransition(StateAwareInterface $subject, $transition)
    {
        return $this->stateMachineFactory->make(
            [
                'initial_state' => $subject->getState()->get('status'),
                'transitions'   => $this->_getTransitions(),
                'event_params'  => [
                    'booking' => $subject
                ]
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getNewSubject(
        StateAwareInterface $subject,
        $transition,
        ReadableStateMachineInterface $stateMachine
    ) {
        $status = $stateMachine->getState();
        $state  = $subject->getState();
        $data   = iterator_to_array($state);

        $data['status'] = $status;

        return $this->_getStateAwareFactory()->make([
            StateAwareFactoryInterface::K_DATA => $data,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * Overridden by this class to pass `$this` as the transitioner, if not given.
     *
     * @since [*next-version*]
     */
    protected function _throwTransitionerException(
        $message = null,
        $code = null,
        RootException $previous = null
    ) {
        throw $this->_createTransitionerException(
            $message,
            $code,
            $previous,
            $this
        );
    }

    /**
     * {@inheritdoc}
     *
     * Overridden by this class to pass `$this` as the transitioner.
     *
     * @since [*next-version*]
     */
    protected function _throwCouldNotTransitionException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $subject = null,
        $transition = null
    ) {
        throw $this->_createCouldNotTransitionException($message,
            $code,
            $previous,
            $this,
            $subject,
            $transition
        );
    }
}
