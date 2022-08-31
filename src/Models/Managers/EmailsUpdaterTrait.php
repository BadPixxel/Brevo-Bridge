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

namespace BadPixxel\SendinblueBridge\Models\Managers;

use BadPixxel\SendinblueBridge\Entity\AbstractEmailStorage as EmailStorage;
use Exception;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Model\GetEmailEventReport;

/**
 * Functions Collection to Update Emails Metadatas from SendInBlue API.
 */
trait EmailsUpdaterTrait
{
    /**
     * Update Email Events List from Smtp Api.
     */
    protected function updateEvents(EmailStorage &$storageEmail, bool $force): self
    {
        //==============================================================================
        // Check if Events Refresh is Allowed
        if (!$force && !$this->getConfig()->isRefreshMetadataAllowed()) {
            return $this;
        }
        //==============================================================================
        // Check if Events Refresh is Needed
        if (!$force && !$storageEmail->isEventOutdated()) {
            return $this;
        }
        //==============================================================================
        // Collect Events
        $events = $this->getEventsFromApi($storageEmail->getMessageId(), $storageEmail->getEmail());
        if (empty($events)) {
            if (!empty($storageEmail->getEvents())) {
                return $this;
            }

            return $this->updateSendEmailEventsErrored($storageEmail);
        }
        //==============================================================================
        // Update Storage
        return $this->updateSendEmailEvents($storageEmail, $events);
    }

    /**
     * Update Email Contents from Smtp Api.
     */
    protected function updateContents(EmailStorage &$storageEmail, bool $force): self
    {
        //==============================================================================
        // Only if Events Refresh is Allowed
        if (!$this->getConfig()->isRefreshContentsAllowed()) {
            return $this;
        }
        //==============================================================================
        // Check if Contents Refresh is Needed
        if (!$force && !empty($storageEmail->getHtmlContent())) {
            return $this;
        }
        //==============================================================================
        // Ensure we have Email UUID
        $uuid = $storageEmail->getUuid();
        if (empty($uuid)) {
            $uuid = $this->getEmailUuid($storageEmail->getMessageId());
        }
        if (empty($uuid)) {
            return $this;
        }
        //==============================================================================
        // Collect Html contents
        $htmlContents = $this->getEmailContents($uuid);
        if (empty($htmlContents) || ("Mail content not available" == $htmlContents)) {
            return $this;
        }
        //==============================================================================
        // Update Storage
        $this->updateSendEmailContents($storageEmail, $uuid, $htmlContents);

        return $this;
    }

    /**
     * Collect Email Events from Smtp Api.
     *
     * @param string $messageId
     * @param string $email
     *
     * @return array
     */
    protected function getEventsFromApi(string $messageId, string $email): array
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
    protected function getEmailContents(string $uuid): ?string
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
        $decoded = json_decode($response);
        if (isset($decoded->body)) {
            return $decoded->body;
        }

        return null;
    }

    /**
     * Collect Email Events from Smtp Api.
     */
    protected function getEmailUuid(string $messageId): ?string
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
        $decoded = json_decode($response);
        if (isset($decoded->transactionalEmails[0]->uuid)) {
            return $decoded->transactionalEmails[0]->uuid;
        }

        return null;
    }
}
