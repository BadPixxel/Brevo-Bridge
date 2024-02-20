<?php

/*
 *  Copyright (C) BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\BrevoBridge\Models;

use BadPixxel\BrevoBridge\Services\Events\EventManager;
use Exception;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract Class for Sending SIB Track Events.
 */
abstract class AbstractTrackEvent
{
    /**
     * Event Type Code.
     *
     * @var string
     */
    protected string $type = "myEventCode";

    /**
     * User Email.
     *
     * @var string
     */
    protected string $userEmail;

    /**
     * Default Properties.
     *
     * @var array
     */
    protected array $propertiesDefaults = array();

    /**
     * Default Event Data.
     *
     * @var array
     */
    protected array $eventDataDefaults = array();

    /**
     * Event Properties.
     * This object will content all your custom fields. Add as many as needed.
     * Keep in mind that those user properties will populate your database
     * on the Marketing Automation platform to create rich scenarios
     *
     * @var null|array<string, string>
     */
    private ?array $properties;

    /**
     * Event Data.
     * This object will contain all additional data you want to pass.
     * It has three file ids' of type string with a unique number
     * and data which can be later parametrised in a smtp template.
     *
     * @var null|array<string, string>
     */
    private ?array $eventdata;

    /**
     * Construct the Event.
     *
     * @param User $user Target User
     */
    public function __construct(User $user)
    {
        $this
            ->setUser($user)
        ;
    }

    /**
     * Send a User Event.
     *
     * @param User $user Target User
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function send(User $user): bool
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getInstance');
        if (!is_callable($callback)) {
            return false;
        }
        //==============================================================================
        // Create a New Instance of the Event
        /** @var AbstractTrackEvent $instance */
        $instance = call_user_func_array($callback, func_get_args());

        //==============================================================================
        // Create a New Instance of the Event
        return $instance->sendEvent(false);
    }

    /**
     * Send a Transactional Test/Demo Event.
     *
     * @param User $user Target User
     *
     * @throws Exception
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function sendDemo(User $user): bool
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getDemoInstance');
        if (!is_callable($callback)) {
            return false;
        }
        //==============================================================================
        // Create a New Instance of the Event
        /** @var AbstractTrackEvent $instance */
        $instance = call_user_func_array($callback, func_get_args());

        //==============================================================================
        // Send Event to SendInBlue API
        return $instance->sendEvent(true);
    }

    /**
     * Get Curl Posted Data (serialized)
     *
     * @return string
     */
    public function getPostFields(): string
    {
        $postFields = array(
            'event' => $this->type,
            'email' => $this->userEmail,
        );
        if (!empty($this->properties)) {
            $postFields['properties'] = $this->properties;
        }
        if (!empty($this->eventdata)) {
            $postFields['properties'] = $this->properties;
        }

        return (string) json_encode($postFields);
    }

    /**
     * @return string
     */
    public static function getLastError(): string
    {
        return EventManager::getInstance()->getLastError();
    }

    //==============================================================================
    // BASIC GETTERS
    //==============================================================================

    /**
     * Set Event User
     *
     * @param User $user
     *
     * @return self
     */
    public function setUser(User $user): self
    {
        $this->userEmail = (string) $user->getEmailCanonical();

        return $this;
    }

    /**
     * Get Event user Email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * Create Event Instance.
     *
     * @param User $user
     *
     * @return self
     */
    protected static function getInstance(User $user): self
    {
        $class = static::class;

        return new $class($user);
    }

    /**
     * Create Demo Event Instance.
     *
     * @param User $user
     *
     * @return self
     */
    protected static function getDemoInstance(User $user): self
    {
        return self::getInstance($user);
    }

    //==============================================================================
    // BASIC EVENTS CRUD ACTIONS
    //==============================================================================

    /**
     * Send Event
     *
     * @return bool
     */
    protected function sendEvent(bool $demoMode): bool
    {
        //==============================================================================
        // Verify Email Before Sending
        if (!$this->isValid()) {
            throw new Exception("Event Validation Fail");
        }

        return $this->getEventManager()->send($this, $demoMode);
    }

    //==============================================================================
    // OTHERS CORE ACTIONS
    //==============================================================================

    /**
     * Static Access to Event Service.
     *
     * @return EventManager
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getEventManager(): EventManager
    {
        return EventManager::getInstance();
    }

    //==============================================================================
    // EVENT VALIDATION (DONE BEFORE SEND)
    //==============================================================================

    /**
     * Validate Event Configuration before Sending
     *
     * @return bool
     */
    private function isValid(): bool
    {
        //==============================================================================
        // Verify Event Code & User Email
        if (empty($this->type) || empty($this->userEmail)) {
            return false;
        }

        return $this->verifyProperties() && $this->verifyEventData();
    }

    /**
     * Validate Event Properties
     *
     * @return bool
     */
    private function verifyProperties(): bool
    {
        try {
            //==============================================================================
            // Init Parameters Resolver
            $resolver = new OptionsResolver();
            $resolver->setDefaults($this->propertiesDefaults);
            //==============================================================================
            // Resolve
            /** @var array<string, string> $properties */
            $properties = $resolver->resolve((array) $this->properties);
            $this->properties = empty($properties) ? null : $properties;
        } catch (Exception $ex) {
            echo $ex->getMessage();

            return false;
        }

        return true;
    }

    /**
     * Validate Event Data
     *
     * @return bool
     */
    private function verifyEventData(): bool
    {
        try {
            //==============================================================================
            // Init Parameters Resolver
            $resolver = new OptionsResolver();
            $resolver->setDefaults($this->eventDataDefaults);
            //==============================================================================
            // Resolve
            /** @var array<string, string> $eventdata */
            $eventdata = $resolver->resolve((array) $this->eventdata);
            $this->eventdata = empty($eventdata) ? null : $eventdata;
        } catch (Exception $ex) {
            echo $ex->getMessage();

            return false;
        }

        return true;
    }
}
