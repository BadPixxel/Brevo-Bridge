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

namespace BadPixxel\SendinblueBridge\Models\Managers;

use SendinBlue\Client\ApiException;

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
     * @param Exception $ex
     *
     * @return null
     */
    protected function catchError(ApiException $ex)
    {
        $this->lastError = $ex->getMessage();

        $response = $ex->getResponseBody();
        if (isset($response->message)) {
            $this->lastError .= ' : '.$response->message;
        }

        return null;
    }
}
