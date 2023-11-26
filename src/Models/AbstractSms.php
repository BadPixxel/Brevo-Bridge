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

use BadPixxel\BrevoBridge\Models\Email\OptionsResolverTrait;
use BadPixxel\BrevoBridge\Models\Sms\SendToUserTrait;
use BadPixxel\BrevoBridge\Services\Sms\SmsManager;
use Brevo\Client\Model\SendSms;
use Brevo\Client\Model\SendTransacSms;
use Sonata\UserBundle\Model\UserInterface as User;

/**
 * Abstract Class for Sending Sms.
 */
abstract class AbstractSms
{
    use SendToUserTrait;
    use OptionsResolverTrait;

    /**
     * Current Sms.
     *
     * @var SendTransacSms
     */
    protected SendTransacSms $sms;

    /**
     * Parameters.
     *
     * @var array<string, mixed>
     */
    protected array $parameters = array();

    //==============================================================================
    // GENERIC SMS INTERFACES
    //==============================================================================

    /**
     * Configure Email with User Parameters
     *
     * @param array<string, mixed> $args
     *
     * @return $this
     */
    abstract public function configure(array $args): static;

    /**
     * Generate Sms Contents
     *
     * @return string
     */
    abstract function getContents(): string;

    /**
     * Get Arguments for Building Fake Sms.
     *
     * @return array<string, mixed>
     */
    abstract public function getFakeArguments(): array;

    /**
     * Send a Transactional Sms.
     */
    final public static function send(User $toUser, mixed ...$args): ?SendSms
    {
        //==============================================================================
        // Create a New Instance of the Sms
        $instance = self::getManager()->getSms(static::class);
        if (!$instance) {
            return null;
        }
        //==============================================================================
        // Send Sms
        return self::getManager()
            ->send($instance, $toUser, $args)
        ;
    }

    /**
     * Send a Transactional Test/Demo Sms.
     */
    public static function sendDemo(User $toUser): ?SendSms
    {
        //==============================================================================
        // Create a New Instance of the Email
        $instance = self::getManager()->getSms(static::class);
        if (!$instance) {
            return null;
        }
        //==============================================================================
        // Send Demo Email
        return self::getManager()
            ->send($instance, $toUser, $instance->getFakeArguments(),true)
        ;
    }

    /**
     * @return string
     */
    public static function getLastError(): string
    {
        return SmsManager::getInstance()->getLastError();
    }

    //==============================================================================
    // BASIC GETTERS
    //==============================================================================

    public function __toString(): string
    {
        return static::class;
    }

    /**
     * Setup Base Transactional Sms
     */
    final public function setSms(SendTransacSms $sms): static
    {
        $this->sms = $sms;

        return $this;
    }

    /**
     * @return SendTransacSms
     */
    final public function getSms(): SendTransacSms
    {
        return $this->sms;
    }

    /**
     * Set an Sms Parameter
     */
    final public function setParameter(string $key, mixed $value): static
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * Get All Sms Parameters
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set All Sms Parameters
     *
     * @param array<string, mixed> $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Merge Sms Current Parameters with Extra Parameters
     *
     * @param array $parameters
     *
     * @return $this
     */
    final public function mergeParams(array $parameters): static
    {
        $this->parameters = array_replace_recursive(
            $this->parameters,
            $parameters
        );

        return $this;
    }

    //==============================================================================
    // OTHERS CORE ACTIONS
    //==============================================================================

    /**
     * Static Access to SMS Manager.
     */
    final protected static function getManager(): SmsManager
    {
        return SmsManager::getInstance();
    }

}
