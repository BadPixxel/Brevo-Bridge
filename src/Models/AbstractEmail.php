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

use BadPixxel\BrevoBridge\Services\SmtpManager;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Exception;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface as User;

/**
 * Abstract Class for Sending Emails.
 */

abstract class AbstractEmail extends GenericEvent
{
    /**
     * Current User.
     *
     * @var User[]
     */
    protected array $toUsers;

    /**
     * Current Email.
     *
     * @var SendSmtpEmail
     */
    protected SendSmtpEmail $email;

    /**
     * Default Parameters.
     *
     * @var array
     */
    protected array $paramsDefaults = array();

    /**
     * Default Parameters Types.
     *
     * @var array
     */
    protected array $paramsTypes = array();

    /**
     * Construct Minimal Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     */
    public function __construct($toUsers)
    {
        parent::__construct();
        $this
            ->create()
            ->setupToUsers($toUsers)
        ;
    }

    /**
     * Create Email Instance in Demo Mode.
     *
     * @param User|User[] $toUsers
     *
     * @return self
     */
    abstract public static function getDemoInstance(array|User $toUsers): self;

    /**
     * Send a Transactional Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return null|CreateSmtpEmail
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function send(array|User $toUsers): ?CreateSmtpEmail
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getInstance');
        if (!is_callable($callback)) {
            return null;
        }
        //==============================================================================
        // Create a New Instance of the Email
        /** @var AbstractEmail $instance */
        $instance = call_user_func_array($callback, func_get_args());

        //==============================================================================
        // Create a New Instance of the Email
        return $instance->sendEmail(false);
    }

    /**
     * Send a Transactional Test/Demo Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return null|CreateSmtpEmail
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function sendDemo(array|User $toUsers): ?CreateSmtpEmail
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getDemoInstance');
        if (!is_callable($callback)) {
            return null;
        }
        //==============================================================================
        // Create a New Instance of the Email
        /** @var AbstractEmail $instance */
        $instance = call_user_func_array($callback, func_get_args());

        //==============================================================================
        // Create a New Instance of the Email
        return $instance->sendEmail(true);
    }

    /**
     * @return string
     */
    public static function getLastError(): string
    {
        return SmtpManager::getInstance()->getLastError();
    }

    //==============================================================================
    // BASIC GETTERS
    //==============================================================================

    /**
     * @return SendSmtpEmail
     */
    public function getEmail(): SendSmtpEmail
    {
        return $this->email;
    }

    //==============================================================================
    // BASIC EMAIL CRUD ACTIONS
    //==============================================================================

    /**
     * Create a New Email and Populate Defaults Values.
     *
     * @return self
     */
    protected function create(): self
    {
        $this->email = $this->getSmtpManager()->create();

        return $this;
    }

    /**
     * Create a New Email and Populate Defaults Values.
     *
     * @param bool $demoMode
     *
     * @throws Exception
     *
     * @return null|CreateSmtpEmail
     */
    protected function sendEmail(bool $demoMode): ?CreateSmtpEmail
    {
        //==============================================================================
        // Apply Processors to the Email
        $this->getSmtpManager()->process($this);
        //==============================================================================
        // Verify Email Before Sending
        if (!$this->isValid()) {
            throw new Exception("Email Validation Fail");
        }
        //==============================================================================
        // Démo? Add Subject Prefix
        if ($demoMode) {
            $this->email->setSubject("[Test]".$this->email->getSubject());
        }

        return $this->getSmtpManager()->send($this->toUsers, $this->email, $demoMode);
    }

    /**
     * Add User to Email To.
     *
     * @param User $toUser
     *
     * @return self
     */
    protected function addToUser(User $toUser): self
    {
        $this->email->setTo(self::addUserEmail($this->email->getTo(), $toUser));

        return $this;
    }

    /**
     * Add User to Email Cc.
     *
     * @param User $ccUser
     *
     * @return self
     */
    protected function addCcUser(User $ccUser): self
    {
        $this->email->setCc(self::addUserEmail($this->email->getCc(), $ccUser));

        return $this;
    }

    /**
     * Add User to Email Bcc.
     *
     * @param User $bccUser
     *
     * @return self
     */
    protected function addBccUser(User $bccUser): self
    {
        $this->email->setBcc(self::addUserEmail($this->email->getBcc(), $bccUser));

        return $this;
    }

    /**
     * Configure Options for Parameters Resolver
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    protected function configureParameters(OptionsResolver $resolver): void
    {
        $resolver->setDefaults($this->paramsDefaults);
        foreach ($this->paramsTypes as $key => $types) {
            $resolver->setAllowedTypes($key, $types);
        }
    }

    //==============================================================================
    // OTHERS CORE ACTIONS
    //==============================================================================

    /**
     * Static Access to Mailer Service.
     *
     * @return SmtpManager
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getSmtpManager(): SmtpManager
    {
        return SmtpManager::getInstance();
    }

    /**
     * Add User to an Emails List.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return $this
     */
    private function setupToUsers(array|User $toUsers): static
    {
        //==============================================================================
        // Ensure we have a List of Users
        if (!is_array($toUsers)) {
            $toUsers = array($toUsers);
        }
        //==============================================================================
        // Verify & Store List of Target Users
        $this->toUsers = array();
        foreach ($toUsers as $toUser) {
            if (!$toUser instanceof User) {
                continue;
            }
            $this->addToUser($toUser);
            $this->toUsers[] = $toUser;
        }

        return $this;
    }

    /**
     * Add User to an Emails List.
     *
     * @param array $emailList
     * @param User  $toUser
     *
     * @return array
     */
    private static function addUserEmail(?array $emailList, User $toUser): array
    {
        //==============================================================================
        // Extract User Name
        $name = method_exists($toUser, "__toString")
            ? $toUser->__toString()
            : $toUser->getUserIdentifier()
        ;
        //==============================================================================
        // Extract User Email
        $email = method_exists($toUser, "getEmailCanonical")
            ? $toUser->getEmailCanonical()
            : $toUser->getUserIdentifier()
        ;
        //==============================================================================
        // Create To User Array
        $emailUser = array(array('name' => $name, 'email' => $email));
        //==============================================================================
        // Push User to List
        if (empty($emailList)) {
            return $emailUser;
        }

        return array_merge($emailList, $emailUser);
    }

    //==============================================================================
    // EMAIL VALIDATION (DONE BEFORE SEND)
    //==============================================================================

    /**
     * Validate Email Configuration before Sending
     *
     * @return bool
     */
    private function isValid(): bool
    {
        //==============================================================================
        // Verify Sender
        /** @var null|SendSmtpEmailSender $sender */
        $sender = $this->email->getSender();
        if (empty($sender)) {
            return false;
        }
        //==============================================================================
        // Verify To
        if (empty($this->email->getTo())) {
            return false;
        }
        //==============================================================================
        // Verify Subject
        if (empty($this->email->getSubject())) {
            return false;
        }
        //==============================================================================
        // Verify Template & Parameters
        if (!empty($this->email->getTemplateId())) {
            //==============================================================================
            // Verify Parameters
            return $this->verifyParameters();
        }

        return true;
    }

    /**
     * @return bool
     */
    private function verifyParameters(): bool
    {
        try {
            //==============================================================================
            // Init Parameters Resolver
            $resolver = new OptionsResolver();
            $this->configureParameters($resolver);
            //==============================================================================
            // Resolve
            $emailParams = (array) $this->email->getParams();
            $this->email->setParams((object) $resolver->resolve($emailParams));
        } catch (Exception $ex) {
            echo $ex->getMessage();

            return false;
        }

        return true;
    }
}
