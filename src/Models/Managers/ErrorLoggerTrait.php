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

use SendinBlue\Client\ApiException;
use stdClass;

/**
 * Manage Errors Logging for Sendinblue Services
 */
trait ErrorLoggerTrait
{
    /**
     * @var null|string
     */
    private $lastError;

    /**
     * @return string
     */
    public function getLastError()
    {
        return (string) $this->lastError;
    }

    /**
     * @param string $error
     *
     * @return null
     */
    protected function setError(string $error)
    {
        $this->lastError = $error;

        return null;
    }

    /**
     * @param ApiException $exception
     *
     * @return null
     */
    protected function catchError(ApiException $exception)
    {
        $this->lastError = $exception->getMessage();

        /** @var null|stdClass $response */
        $response = $exception->getResponseBody();
        if (isset($response->message)) {
            $this->lastError .= ' : '.$response->message;
        }

        return null;
    }
}
