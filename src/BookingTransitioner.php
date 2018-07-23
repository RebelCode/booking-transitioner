<?php

namespace RebelCode\Bookings;

use Dhii\Data\CreateCouldNotTransitionExceptionCapableTrait;
use Dhii\Data\CreateTransitionerExceptionCapableTrait;
use Dhii\Data\StateAwareFactoryInterface;
use Dhii\Data\StateAwareInterface;
use Dhii\Data\TransitionCapableStateMachineTrait;
use Dhii\Data\TransitionerInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\State\ReadableStateMachineInterface;
use Dhii\State\StateMachineAwareTrait;
use Dhii\State\StateMachineInterface;
use Exception as RootException;

/**
 * Implementation of a booking transitioner, that uses a state machine to determine the new booking status.
 *
 * @since [*next-version*]
 */
class BookingTransitioner implements TransitionerInterface
{
    /* @since [*next-version*] */
    use TransitionCapableStateMachineTrait;

    /* @since [*next-version*] */
    use StateMachineAwareTrait;

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
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param StateAwareFactoryInterface $stateAwareFactory The state-aware factory instance.
     * @param StateMachineInterface      $stateMachine      The state machine instance.
     */
    public function __construct(StateAwareFactoryInterface $stateAwareFactory, StateMachineInterface $stateMachine)
    {
        $this->_setStateAwareFactory($stateAwareFactory);
        $this->_setStateMachine($stateMachine);
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
        return $this->_getStateMachine();
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
