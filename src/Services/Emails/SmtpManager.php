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

namespace BadPixxel\BrevoBridge\Services\Emails;

use BadPixxel\BrevoBridge\Models\Managers;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\GetEmailEventReport;
use Brevo\Client\Model\SendSmtpEmail;
use Exception;
use GuzzleHttp\Client;
use stdClass;

/**
 * Smtp Emails Manager for Brevo Api.
 */
class SmtpManager
{
    use Managers\ErrorLoggerTrait;

    /**
     * Transactional Emails API Service.
     *
     * @var null|TransactionalEmailsApi
     */
    protected ?TransactionalEmailsApi $smtpApi;

    /**
     * @var SmtpManager
     */
    private static SmtpManager $staticInstance;

    public function __construct(
        private readonly Configuration $config,
        private readonly EmailsStorage $storage,
    ) {
        //==============================================================================
        // Store Static Instance for Access as Static
        self::$staticInstance = $this;
    }

    /**
     * Static Access to this Service.
     *
     * @return SmtpManager
     */
    public static function getInstance(): SmtpManager
    {
        return self::$staticInstance;
    }

    /**
     * Create a new Transactional Email.
     *
     * @return SendSmtpEmail
     */
    public function newSmtpEmail(): SendSmtpEmail
    {
        //==============================================================================
        // Create new Smtp Email
        $newEmail = new SendSmtpEmail();
        //==============================================================================
        // Setup Default Email Values
        $newEmail
            ->setSender($this->config->getDefaultSender())
            ->setReplyTo($this->config->getDefaultReplyTo())
            ->setTo(array())
        ;

        return $newEmail;
    }

    /**
     * Send a Transactional Email from Api.
     *
     * @param array         $toUser
     * @param SendSmtpEmail $sendEmail
     * @param bool          $demoMode
     *
     * @return null|CreateSmtpEmail
     */
    public function send(array $toUser, SendSmtpEmail $sendEmail, bool $demoMode): ?CreateSmtpEmail
    {
        try {
            //==============================================================================
            // Check if Sending Emails is Allowed
            if (!$demoMode && !$this->config->isSendAllowed()) {
                return $this->setError('Brevo API is Disabled');
            }
            //==============================================================================
            // Check if THIS Email was Already Send
            $filteredUsers = $this->storage->filterAlreadySendUsers($toUser, $sendEmail, $demoMode);
            if (!$filteredUsers) {
                return $this->setError('This Email has Already been Send...');
            }
            //==============================================================================
            // Send the Email
            $createEmail = $this->getApi()->sendTransacEmail($sendEmail);
            //==============================================================================
            // Save the Email to DataBase
            $this->storage->saveSendEmail($filteredUsers, $sendEmail, $createEmail);
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }

        return $createEmail;
    }

    /**
     * Collect Email Events from Smtp Api.
     *
     * @param string $messageId
     * @param string $email
     *
     * @return array
     */
    public function getEvents(string $messageId, string $email): array
    {
        //==============================================================================
        // Collect Events for this Message via Smtp Api
        try {
            $eventsReport = $this
                ->getApi()
                ->getEmailEventReport(10, 0, null, null, null, $email, null, null, $messageId);
        } catch (ApiException $ex) {
            $this->catchError($ex);

            return array();
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());

            return array();
        }
        //==============================================================================
        // Safety Checks
        if (!($eventsReport instanceof GetEmailEventReport)) {
            return array();
        }
        /** @var null|array $events */
        $events = $eventsReport->getEvents();

        return is_array($events) ? $events : array();
    }

    /**
     * Collect Email Events from Smtp Api.
     */
    public function getContents(string $uuid): ?string
    {
        $host = $this->getApi()->getConfig()->getHost();
        $apiKey = $this->getApi()->getConfig()->getApiKey('api-key');
        //==============================================================================
        // Safety Checks
        if (empty($host) || empty($apiKey) || empty($uuid)) {
            return null;
        }
        //==============================================================================
        // Collect Events via RAW CURL REQUEST
        // This action isn't implemented on API
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $host.'/smtp/emails/'.$uuid,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'api-key: '.$apiKey,
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        //==============================================================================
        // Errors Checks
        if ($err || !is_string($response)) {
            return null;
        }
        //==============================================================================
        // Parse response
        /** @var null|stdClass $decoded */
        $decoded = json_decode($response);
        if (isset($decoded->body)) {
            return $decoded->body;
        }

        return null;
    }

    /**
     * Get Email Uuid from Message ID using Smtp Api.
     */
    public function getUuid(string $messageId): ?string
    {
        $host = $this->getApi()->getConfig()->getHost();
        $apiKey = $this->getApi()->getConfig()->getApiKey('api-key');
        //==============================================================================
        // Safety Checks
        if (empty($host) || empty($apiKey)) {
            return null;
        }
        //==============================================================================
        // Collect Events via RAW CURL REQUEST
        // This action isn't implemented on API
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $host.'/smtp/emails?messageId='.$messageId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'api-key: '.$apiKey,
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        //==============================================================================
        // Errors Checks
        if ($err || !is_string($response)) {
            return null;
        }
        //==============================================================================
        // Parse response
        /** @var null|stdClass $decoded */
        $decoded = json_decode($response);
        if (isset($decoded->transactionalEmails[0]->uuid)) {
            return $decoded->transactionalEmails[0]->uuid;
        }

        return null;
    }

    /**
     * Access to Brevo API Service.
     *
     * @return TransactionalEmailsApi
     */
    private function getApi(): TransactionalEmailsApi
    {
        if (!isset($this->smtpApi)) {
            $this->smtpApi = new TransactionalEmailsApi(
                new Client(),
                $this->config->getSdkConfig()
            );
        }

        return $this->smtpApi;
    }
}
