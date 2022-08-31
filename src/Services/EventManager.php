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

namespace BadPixxel\SendinblueBridge\Services;

use BadPixxel\SendinblueBridge\Models\AbstractTrackEvent;
use BadPixxel\SendinblueBridge\Services\ConfigurationManager as Configuration;
use Exception;

/**
 * Tracker Events Manager for SendingBlue Api.
 */
class EventManager
{
    use \BadPixxel\SendinblueBridge\Models\Managers\ErrorLoggerTrait;

    /**
     * Bridge Configuration.
     *
     * @var ConfigurationManager
     */
    private $config;

    /**
     * @var EventManager
     */
    private static $staticInstance;

    /**
     * @param Configuration $config
     */
    public function __construct(Configuration $config)
    {
        //==============================================================================
        // Connect to Bridge Configuration Service
        $this->config = $config;
        //==============================================================================
        // Store Static Instance for Access as Static
        static::$staticInstance = $this;
    }

    /**
     * Static Access to this Service.
     *
     * @return EventManager
     */
    public static function getInstance(): EventManager
    {
        return static::$staticInstance;
    }

    /**
     * Send a Website Event from REST Api.
     *
     * @param AbstractTrackEvent $event
     * @param bool               $demoMode
     *
     * @return bool
     */
    public function send(AbstractTrackEvent $event, bool $demoMode): bool
    {
        try {
            //==============================================================================
            // Check if Sending Emails is Allowed
            if (!$demoMode && !$this->config->isSendAllowed()) {
                return (bool) $this->setError('SendInBlue API is Disabled');
            }
            //==============================================================================
            // Check Tracker Key is Defined
            $curlConfig = $this->config->getEventsCurlConfig();
            if (empty($curlConfig)) {
                return (bool) $this->setError('SendInBlue Events are Disabled');
            }
            //==============================================================================
            // Send Curl Request
            $curl = curl_init();
            curl_setopt_array($curl, array_replace_recursive(
                $curlConfig,
                array(CURLOPT_POSTFIELDS => $event->getPostFields())
            ));
            curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                return (bool) $this->setError("cURL Error #:".$err);
            }
        } catch (Exception $ex) {
            return (bool) $this->setError($ex->getMessage());
        }

        return true;
    }

    /**
     * Find an Event Class by Code
     *
     * @param string $eventCode
     *
     * @return null|string
     */
    public function getEventByCode(string $eventCode): ?string
    {
        return $this->config->getEventByCode($eventCode);
    }

    /**
     * Find All Available Event Class
     *
     * @return array
     */
    public function getAllEvents(): array
    {
        return $this->config->getAllEvents();
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
}
