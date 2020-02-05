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
     * @var User
     */
    protected $toUser;

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
    protected $defaultParams = array();

    /**
     * Construct Minimal Email.
     *
     * @param User $toUser
     */
    public function __construct(User $toUser)
    {
        $this
            ->create()
            ->addToUser($toUser)
        ;

        $this->toUser = $toUser;
    }

    /**
     * Send a Transactionnal Email.
     */
    public static function send(): ?CreateSmtpEmail
    {
        //==============================================================================
        // Create a New Instance of the Email
        $instance = call_user_func_array(array(static::class, 'getInstance'), func_get_args());
        //==============================================================================
        // Create a New Instance of the Email
        return $instance->sendEmail(false);
    }

    /**
     * Send a Transactionnal Test/Demo Email.
     */
    public static function sendDemo(): ?CreateSmtpEmail
    {
        //==============================================================================
        // Create a New Instance of the Email
        $instance = call_user_func_array(array(static::class, 'getDemoInstance'), func_get_args());
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
     * @return self
     */
    protected function sendEmail(bool $demoMode): ?CreateSmtpEmail
    {
        //==============================================================================
        // Verify Email Before Sending
        if (!$this->isValid()) {
            return null;
        }

        return $this->getSmtpManager()->send($this->toUser, $this->email, $demoMode);
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
     * Add User to Email ReplyTo.
     *
     * @return self
     */
    protected function addReplyToUser(User $ccUser): self
    {
        $this->email->setReplyTo(self::addUserEmail($this->email->getReplyTo(), $ccUser));

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
        $resolver->setDefaults($this->defaultParams);
    }

    //==============================================================================
    // OTHERS CORE ACTIONS
    //==============================================================================

    /**
     * Static Access to Mailer Service.
     *
     * @return SmtpManager
     */
    protected function getSmtpManager(): SmtpManager
    {
        return SmtpManager::getInstance();
    }

    /**
     * Add User to an Emails List.
     *
     * @return self
     */
    private static function addUserEmail(?array $emailList, User $toUser): array
    {
        $emailUser = array(array(
            'name' => $toUser->__toString(),
            'email' => $toUser->getEmailCanonical(),
        ));

        if (is_null($emailList) || empty($emailList)) {
            return $emailUser;
        }

        return array_merge($emailList, $emailUser);
    }

    //==============================================================================
    // EMAIL VALIDATION (DONE BEFORE SEND)
    //==============================================================================

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
        // Verify Template Parameters
        if (!empty($this->email->getTemplateId())) {
        }

        return true;
    }

    /**
     * @return bool
     */
    private function verifyParameters(): bool
    {
        try {
            $resolver = new OptionsResolver();
            $this->configureParameters($resolver);
            $this->email->setParams($resolver->resolve($this->email->getParams()));
        } catch (Exception $ex) {
            return false;
        }

        return true;
    }
}
