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

namespace BadPixxel\BrevoBridge\Services;

use BadPixxel\BrevoBridge\Models\Managers;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Api\TransactionalSMSApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\SendSms;
use Brevo\Client\Model\SendTransacSms;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Exception;
use GuzzleHttp\Client;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\Routing\RouterInterface as Router;
use Symfony\Contracts\Translation\TranslatorInterface as Translator;
use Twig\Environment as Twig;

/**
 * Sms Manager for Brevo Api.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SmsManager
{
    use Managers\ErrorLoggerTrait;
    use Managers\TemplatingTrait;
    use Managers\SmsStorageTrait;

    /**
     * Transactional Sms API Service.
     *
     * @var null|TransactionalSMSApi
     */
    protected ?TransactionalSMSApi $smsApi;

    /**
     * Bridge Configuration.
     *
     * @var ConfigurationManager
     */
    private ConfigurationManager $config;

    /**
     * @var SmsManager
     */
    private static SmsManager $staticInstance;

    /**
     * @param Configuration $config
     * @param EntityManager $doctrine
     * @param Twig          $twig
     * @param Translator    $translator
     * @param Router        $router
     */
    public function __construct(
        Configuration $config,
        EntityManager $doctrine,
        Twig $twig,
        Translator $translator,
        Router $router
    ) {
        //==============================================================================
        // Connect to Bridge Configuration Service
        $this->config = $config;
        //==============================================================================
        // Connect to Storage Services
        $this->setupStorage($doctrine);
        //==============================================================================
        // Connect to Templating Services
        $this->setupTemplating(
            $twig,
            $translator,
            $this->config->configureRouter($router)
        );
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
     * Create a new Transactional Sms.
     *
     * @return SendTransacSms
     */
    public function create(): SendTransacSms
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
                $this->config->getDefaultSender()->getName()
            ))
        ;

        return $newSms;
    }

    /**
     * Send a Transactional Sms from Api.
     *
     * @param User           $user
     * @param SendTransacSms $sendSms
     * @param bool           $demoMode
     *
     * @return null|SendSms
     */
    public function send(User $user, SendTransacSms $sendSms, bool $demoMode): ?SendSms
    {
        try {
            //==============================================================================
            // Check if Sending Sms is Allowed
            if (!$demoMode && !$this->config->isSendAllowed()) {
                return $this->setError('SendInBlue API is Disabled');
            }
            //==============================================================================
            // Check if THIS Sms was Already Send
            if ($this->isAlreadySend($user, $sendSms, $demoMode)) {
                return $this->setError('This Sms has Already been Send...');
            }
            //==============================================================================
            // Send the Sms
            $createSms = $this->getApi()->sendTransacSms($sendSms);
            //==============================================================================
            // Save the Sms to DataBase
            $this->saveSendSms($user, $sendSms, $createSms);
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }

        return $createSms;
    }

    /**
     * Find a Sms Class by Code
     *
     * @param string $smsCode
     *
     * @return null|string
     */
    public function getSmsByCode(string $smsCode): ?string
    {
        return $this->config->getSmsByCode($smsCode);
    }

    /**
     * Find All Available Sms Class
     *
     * @return array
     */
    public function getAllSms(): array
    {
        return $this->config->getAllSms();
    }

    /**
     * Get Configuration
     *
     * @return Configuration
     */
    protected function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * Access to SendinBlue API Service.
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
