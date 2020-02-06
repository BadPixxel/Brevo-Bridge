<?php

/*
 *  Copyright (C) 2020 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Models;

use BadPixxel\SendinblueBridge\Services\SmtpManager;
use Exception;
use FOS\UserBundle\Model\UserInterface as User;
use SendinBlue\Client\Model\CreateSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmail;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    protected $toUsers;

    /**
     * Current Email.
     *
     * @var SendSmtpEmail
     */
    protected $email;

    /**
     * Default Parameters.
     *
     * @var array
     */
    protected $paramsDefaults = array();

    /**
     * Default Parameters Types.
     *
     * @var array
     */
    protected $paramsTypes = array();

    /**
     * Construct Minimal Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     */
    public function __construct($toUsers)
    {
        $this
            ->create()
            ->setupToUsers($toUsers)
        ;
    }

    /**
     * Send a Transactionnal Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return null|CreateSmtpEmail
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function send($toUsers): ?CreateSmtpEmail
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getInstance');
        if (!is_callable($callback)) {
            return null;
        }
        //==============================================================================
        // Create a New Instance of the Email
        $instance = call_user_func_array($callback, func_get_args());
        //==============================================================================
        // Create a New Instance of the Email
        return $instance->sendEmail(false);
    }

    /**
     * Send a Transactionnal Test/Demo Email.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return null|CreateSmtpEmail
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function sendDemo($toUsers): ?CreateSmtpEmail
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getDemoInstance');
        if (!is_callable($callback)) {
            return null;
        }
        //==============================================================================
        // Create a New Instance of the Email
        $instance = call_user_func_array($callback, func_get_args());
        //==============================================================================
        // Create a New Instance of the Email
        return $instance->sendEmail(true);
    }

    /**
     * @return string
     */
    public static function getLastError()
    {
        return SmtpManager::getInstance()->getLastError();
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
     * @return null|CreateSmtpEmail
     */
    protected function sendEmail(bool $demoMode): ?CreateSmtpEmail
    {
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
     * @return self
     */
    private function setupToUsers($toUsers): self
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
            if (!is_subclass_of($toUser, User::class)) {
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
            : $toUser->getUsername();
        //==============================================================================
        // Create To User Array
        $emailUser = array(array(
            'name' => $name,
            'email' => $toUser->getEmailCanonical(),
        ));
        //==============================================================================
        // Push User to List
        if (is_null($emailList) || empty($emailList)) {
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
        if (empty($this->email->getSender())) {
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
            $this->email->setParams($resolver->resolve($emailParams));
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }
}
