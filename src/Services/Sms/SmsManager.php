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

namespace BadPixxel\BrevoBridge\Services\Sms;

use BadPixxel\BrevoBridge\Dictionary\ServiceTags;
use BadPixxel\BrevoBridge\Helpers\SmsValidator;
use BadPixxel\BrevoBridge\Models\AbstractSms;
use BadPixxel\BrevoBridge\Models\Managers;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Api\TransactionalSMSApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\SendSms;
use Brevo\Client\Model\SendTransacSms;
use Exception;
use GuzzleHttp\Client;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * Sms Manager for Brevo Api.
 */
#[Autoconfigure(public: true)]
class SmsManager
{
    use Managers\ErrorLoggerTrait;

    /**
     * Transactional Sms API Service.
     *
     * @var null|TransactionalSMSApi
     */
    protected ?TransactionalSMSApi $smsApi;

    /**
     * @var SmsManager
     */
    private static SmsManager $staticInstance;

    /**
     * @param AbstractSms[] $sms
     */
    public function __construct(
        private readonly Configuration $config,
        private readonly SmsProcessor $processor,
        private readonly SmsStorage $storage,
        #[TaggedIterator(ServiceTags::SMS)]
        private readonly iterable       $sms,
    ) {
        //==============================================================================
        // Store Static Instance for Access as Static
        self::$staticInstance = $this;
    }

    /**
     * Static Access to this Service.
     *
     * @return SmsManager
     */
    public static function getInstance(): SmsManager
    {
        return self::$staticInstance;
    }

    /**
     * Generate a Fake version of an Email
     */
    public function fake(AbstractSms $sms, User $user): ?AbstractSms
    {
        //==============================================================================
        // Build Sms using Fake Arguments
        return $this->build($sms, $user, $sms->getFakeArguments());
    }

    /**
     * Build Sms.
     *
     * @param AbstractSms $sms    The Sms to Compile
     * @param User        $toUser Target User
     * @param array       $args   User Inputs
     *
     * @return null|AbstractSms
     */
    public function build(AbstractSms $sms, User $toUser, array $args): ?AbstractSms
    {
        //==============================================================================
        // Setup Sms
        $sms
            ->setSms($this->newTransactionalSms())
            ->setToUser($toUser)
            ->configure($args)
        ;
        //==============================================================================
        // Apply Processors to Email
        $this->process($sms);

        //==============================================================================
        // Validate Sms & Parameters
        try {
            $resolvedParams = SmsValidator::validate($sms);
        } catch (Exception $ex) {
            return $this->setError(
                sprintf("Sms Validation Fails: %s", $ex->getMessage())
            );
        }
        //==============================================================================
        // Update Parameters with resolved Values
        $sms->setParameters($resolvedParams);

        return $sms;
    }

    /**
     * Send a Transactional Sms from Api.
     *
     * @param AbstractSms $sms
     * @param User        $toUser
     * @param array       $args     User Inputs
     * @param bool        $demoMode
     *
     * @return null|SendSms
     */
    public function send(AbstractSms $sms, User $toUser, array $args, bool $demoMode = false): ?SendSms
    {
        try {
            //==============================================================================
            // Check if Sending Sms is Allowed
            if (!$demoMode && !$this->config->isSendAllowed()) {
                return $this->setError('Brevo API is Disabled');
            }
            //==============================================================================
            // Build Sms using User Arguments
            if (!$compiledSms = $this->build($sms, $toUser, $args)?->getSms()) {
                return null;
            }
            //==============================================================================
            // Check if THIS Sms was Already Send
            if ($this->storage->isAlreadySend($toUser, $compiledSms, $demoMode)) {
                return $this->setError('This Sms has Already been Send...');
            }
            //==============================================================================
            // Send the Sms
            $sendSms = $this->getApi()->sendTransacSms($compiledSms);
            //==============================================================================
            // Save the Sms to DataBase
            $this->storage->saveSendSms($toUser, $compiledSms, $sendSms);
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }

        return $sendSms;
    }

    /**
     * Find All Available Sms Class
     *
     * @return array<string, AbstractSms>
     */
    public function getAll(): array
    {
        static $list;

        if (!isset($list)) {
            $list = array();
            foreach ($this->sms as $sms) {
                $list[crc32(get_class($sms))] = $sms;
            }
        }

        return $list;
    }

    /**
     * Find a Sms Class by Class
     *
     * @param class-string $smsClass Sms Class
     */
    public function getSms(string $smsClass): ?AbstractSms
    {
        foreach ($this->sms as $sms) {
            if (get_class($sms) == $smsClass) {
                return $sms;
            }
        }

        return null;
    }

    /**
     * Find a Sms Class by ID
     */
    public function getSmsById(string $smsId): ?AbstractSms
    {
        return $this->getAll()[$smsId] ?? null;
    }

    /**
     * Create a new Transactional Sms.
     *
     * @return SendTransacSms
     */
    private function newTransactionalSms(): SendTransacSms
    {
        //==============================================================================
        // Create new Smtp Sms
        $newSms = new SendTransacSms();
        //==============================================================================
        // Setup Default Sms Values
        $newSms
            ->setSender(str_replace(
                array("#", "-", "'", ";", "&"),
                '',
                substr($this->config->getDefaultSender()->getName(), 0, 15)
            ))
        ;

        return $newSms;
    }

    /**
     * Apply Processors to a Transactional Sms.
     */
    private function process(AbstractSms $sms): AbstractSms
    {
        return $this->processor->process($sms);
    }

    /**
     * Access to Brevo API Service.
     *
     * @return TransactionalSMSApi
     */
    private function getApi(): TransactionalSMSApi
    {
        if (!isset($this->smsApi)) {
            $this->smsApi = new TransactionalSMSApi(
                new Client(),
                $this->config->getSdkConfig()
            );
        }

        return $this->smsApi;
    }
}
