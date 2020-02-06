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

namespace BadPixxel\SendinblueBridge\Services;

use SendinBlue\Client\Model\SendSmtpEmailReplyTo;
use SendinBlue\Client\Model\SendSmtpEmailSender;

/**
 * Bridge Configuration Manager for SendingBlue Api.
 */
class ConfigurationManager
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @param array $configuration
     * @param bool  $enabled
     */
    public function __construct(array $configuration, bool $enabled)
    {
        $this->config = $configuration;
        $this->enabled = $enabled;
    }

    /**
     * Allow Sending Emails ?
     *
     * @return bool
     */
    public function isSendAllowed(): bool
    {
        return $this->enabled;
    }

    /**
     * Get Email Storage Class.
     *
     * @return string
     */
    public function getEmailStorageClass(): string
    {
        return $this->config['storage']['emails'];
    }

    /**
     * Get Sms Storage Class.
     *
     * @return string
     */
    public function getSmsStorageClass(): string
    {
        return $this->config['storage']['sms'];
    }

    /**
     * Get Default Email Sender.
     *
     * @return SendSmtpEmailSender
     */
    public function getDefaultSender(): SendSmtpEmailSender
    {
        return new SendSmtpEmailSender(array(
            'name' => $this->config['sender']['name'],
            'email' => $this->config['sender']['email'],
        ));
    }

    /**
     * Get Default Email Sender.
     *
     * @return SendSmtpEmailReplyTo
     */
    public function getDefaultReplyTo(): SendSmtpEmailReplyTo
    {
        return new SendSmtpEmailReplyTo(array(
            'name' => $this->config['reply']['name'],
            'email' => $this->config['reply']['email'],
        ));
    }

    /**
     * Find an Email Class by Code
     *
     * @param string $emailCode
     *
     * @return null|string
     */
    public function getEmailByCode(string $emailCode): ?string
    {
        return isset($this->config['emails'][$emailCode])
            ? $this->config['emails'][$emailCode]
            : null;
    }

    /**
     * Get All Emails Class
     *
     * @return array
     */
    public function getAllEmails(): array
    {
        return $this->config['emails'];
    }
}