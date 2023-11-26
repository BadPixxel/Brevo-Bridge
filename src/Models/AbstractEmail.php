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
use BadPixxel\BrevoBridge\Models\Email\SendToUserTrait;
use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\SendSmtpEmail;
use Symfony\Component\Security\Core\User\UserInterface as User;

/**
 * Abstract Class for Sending Emails.
 */
abstract class AbstractEmail
{
    use SendToUserTrait;
    use OptionsResolverTrait;

    /**
     * Current Email.
     *
     * @var SendSmtpEmail
     */
    protected SendSmtpEmail $email;

    //==============================================================================
    // GENERIC EMAIL INTERFACES
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
     * Get Arguments for Building Fake Email.
     *
     * @return array<string, mixed>
     */
    abstract public function getFakeArguments(): array;

    /**
     * Send a Transactional Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     */
    final public static function send(array|User $toUsers, mixed ...$args): ?CreateSmtpEmail
    {
        //==============================================================================
        // Create a New Instance of the Email
        $instance = self::getManager()->getEmail(static::class);
        if (!$instance) {
            return null;
        }
        //==============================================================================
        // Send Email
        return self::getManager()
            ->send($instance, $toUsers, $args)
        ;
    }

    /**
     * Send a Transactional Test/Demo Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     */
    public static function sendDemo(array|User $toUsers): ?CreateSmtpEmail
    {
        //==============================================================================
        // Create a New Instance of the Email
        $instance = self::getManager()->getEmail(static::class);
        if (!$instance) {
            return null;
        }
        //==============================================================================
        // Send Demo Email
        return self::getManager()
            ->send($instance, $toUsers, $instance->getFakeArguments(),true)
        ;
    }

    /**
     * @return string
     */
    final public static function getLastError(): string
    {
        return self::getManager()->getLastError();
    }

    //==============================================================================
    // BASIC GETTERS
    //==============================================================================

    public function __toString(): string
    {
        return static::class;
    }

    /**
     * Setup Base Smtp Email
     */
    final public function setEmail(SendSmtpEmail $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return SendSmtpEmail
     */
    final public function getEmail(): SendSmtpEmail
    {
        return $this->email;
    }

    //==============================================================================
    // BASIC EMAIL CRUD ACTIONS
    //==============================================================================

    /**
     * Merge Email Current Parameters with Extra Parameters
     *
     * @param array $parameters
     *
     * @return $this
     */
    final public function mergeParams(array $parameters): static
    {
        $this->getEmail()->setParams((object) array_replace_recursive(
            (array) $this->getEmail()->getParams(),
            $parameters
        ));

        return $this;
    }

    //==============================================================================
    // OTHERS CORE ACTIONS
    //==============================================================================

    /**
     * Static Access to Email Manager.
     */
    final protected static function getManager(): EmailsManager
    {
        return EmailsManager::getInstance();
    }
}
