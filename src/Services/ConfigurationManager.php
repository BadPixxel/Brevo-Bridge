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
     * @return array
     */
    public function getDefaultSender(): array
    {
        return array(
            'name' => $this->config['sender']['name'],
            'email' => $this->config['sender']['email'],
        );
    }
}
