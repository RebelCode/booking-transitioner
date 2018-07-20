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
    use TransitionCapableStateMachineTrait, StateMachineAwareTrait {
        // Use `_getStateMachine($subject, $transition)` from `TransitionCapableStateMachineTrait`, instead of the
        // normal parameter-less getter from `StateMachineAwareTrait()`.
        TransitionCapableStateMachineTrait::_getStateMachine insteadof StateMachineAwareTrait;

        // Aliases to allow this class to implement the below methods while also being able to call the original
        // implementations defined in the `StateMachineAwareTrait` trait.
        StateMachineAwareTrait::_getStateMachine as _getInternalStateMachine;
        StateMachineAwareTrait::_setStateMachine as _setInternalStateMachine;
    }

    /* @since [*next-version*] */
    use CreateTransitionerExceptionCapableTrait {
        // Alias to allow this class to override the method and also call the original implementation.
        // Trait name prefix required to disambiguate from the same method in `TransitionCapableStateMachineTrait`.
        CreateTransitionerExceptionCapableTrait::_createTransitionerException as
        _createRealTransitionException;
    }

    /* @since [*next-version*] */
    use CreateCouldNotTransitionExceptionCapableTrait {
        // Alias to allow this class to override the method and also call the original implementation.
        // Trait name prefix required to disambiguate from the same method in `TransitionCapableStateMachineTrait`.
        CreateCouldNotTransitionExceptionCapableTrait::_createCouldNotTransitionException as
        _createRealCouldNotTransitionException;
    }

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
        $this->_setInternalStateMachine($stateMachine);
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
    protected function _getStateMachine(StateAwareInterface $subject, $transition)
    {
        return $this->_getInternalStateMachine();
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
    protected function _createTransitionerException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $transitioner = null
    ) {
        return $this->_createRealTransitionException(
            $message,
            $code,
            $previous,
            $transitioner === null ? $this : $transitioner
        );
    }

    /**
     * {@inheritdoc}
     *
     * Overridden by this class to pass `$this` as the transitioner.
     *
     * @since [*next-version*]
     */
    protected function _createCouldNotTransitionException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $transitioner = null,
        $subject = null,
        $transition = null
    ) {
        return $this->_createRealCouldNotTransitionException($message,
            $code,
            $previous,
            $transitioner === null ? $this : $transitioner,
            $subject,
            $transition
        );
    }
}
