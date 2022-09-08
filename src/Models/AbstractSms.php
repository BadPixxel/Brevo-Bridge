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

namespace BadPixxel\SendinblueBridge\Models;

use BadPixxel\SendinblueBridge\Services\SmsManager;
use Exception;
use FOS\UserBundle\Model\UserInterface as User;
use SendinBlue\Client\Model\SendSms;
use SendinBlue\Client\Model\SendTransacSms;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract Class for Sending Sms.
 */
abstract class AbstractSms
{
    /**
     * Current User.
     *
     * @var User
     */
    protected $user;

    /**
     * Current Sms.
     *
     * @var SendTransacSms
     */
    protected $sms;

    /**
     * Parameters.
     *
     * @var array
     */
    protected $params = array();

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
     * Construct Minimal Sms.
     *
     * @param User $user Target User
     */
    public function __construct(User $user)
    {
        $this
            ->create()
            ->setupToUser($user)
        ;
    }

    /**
     * Send a Transactional Sms.
     *
     * @param User $user Target User
     *
     * @return null|SendSms
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function send(User $user): ?SendSms
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getInstance');
        if (!is_callable($callback)) {
            return null;
        }
        //==============================================================================
        // Create a New Instance of the Email
        /** @var AbstractSms $instance */
        $instance = call_user_func_array($callback, func_get_args());
        //==============================================================================
        // Create a New Instance of the Sms
        return $instance->sendSms(false);
    }

    /**
     * Send a Transactional Test/Demo Sms.
     *
     * @param User $user Target User
     *
     * @return null|SendSms
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function sendDemo(User $user): ?SendSms
    {
        //==============================================================================
        // Prepare Callback
        $callback = array(static::class, 'getDemoInstance');
        if (!is_callable($callback)) {
            return null;
        }
        //==============================================================================
        // Create a New Instance of the Email
        /** @var AbstractSms $instance */
        $instance = call_user_func_array($callback, func_get_args());
        //==============================================================================
        // Create a New Instance of the Sms
        return $instance->sendSms(true);
    }

    /**
     * @return string
     */
    public static function getLastError()
    {
        return SmsManager::getInstance()->getLastError();
    }

    //==============================================================================
    // BASIC GETTERS
    //==============================================================================

    /**
     * @return SendTransacSms
     */
    public function getSms(): SendTransacSms
    {
        return $this->sms;
    }

    //==============================================================================
    // BASIC SMS CRUD ACTIONS
    //==============================================================================

    /**
     * Create a New Sms and Populate Defaults Values.
     *
     * @return self
     */
    protected function create(): self
    {
        $this->sms = $this->getSmsManager()->create();

        return $this;
    }

    /**
     * Create a New Sms and Populate Defaults Values.
     *
     * @return null|SendSms
     */
    protected function sendSms(bool $demoMode): ?SendSms
    {
        //==============================================================================
        // Verify Sms Inputs before Generating Contents
        if (!$this->verifyParameters()) {
            throw new Exception("Sms Parameters are Invalid");
        }
        //==============================================================================
        // Generate Sms Contents
        $this->sms->setContent($this->getContents());

        //==============================================================================
        // Verify Sms Before Sending
        if (!$this->isValid()) {
            throw new Exception("Sms Validation Fail");
        }

        return $this->getSmsManager()->send($this->user, $this->sms, $demoMode);
    }

    /**
     * Generate Sms Contents
     *
     * @throws Exception
     *
     * @return string
     */
    protected function getContents(): string
    {
        throw new Exception(
            "You must override this function to generate"
            ." Sms Contents once input are validated"
        );
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
     * Static Access to SMS Service.
     *
     * @return SmsManager
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getSmsManager(): SmsManager
    {
        return SmsManager::getInstance();
    }

    /**
     * Add User to a Sms List.
     *
     * @param User $user
     *
     * @return self
     */
    private function setupToUser($user): self
    {
        $this->user = $user;
        if (method_exists($user, "getPhone")) {
            $this->sms->setRecipient($user->getPhone());
        }

        return $this;
    }

    //==============================================================================
    // SMS VALIDATION (DONE BEFORE SEND)
    //==============================================================================

    /**
     * Validate SMS Configuration before Sending
     *
     * @return bool
     */
    private function isValid(): bool
    {
        //==============================================================================
        // Verify Sender
        if (empty($this->sms->getSender())) {
            return false;
        }
        //==============================================================================
        // Verify Recipient
        if (empty($this->sms->getRecipient())) {
            return false;
        }
        //==============================================================================
        // Verify Contents
        if (empty($this->sms->getContent())) {
            return false;
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
            $this->params = $resolver->resolve($this->params);
        } catch (Exception $ex) {
            echo $ex->getMessage();

            return false;
        }

        return true;
    }
}
