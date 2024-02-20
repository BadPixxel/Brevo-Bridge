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

use BadPixxel\BrevoBridge\Models\Managers\ErrorLoggerTrait;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Api\AccountApi;
use Brevo\Client\ApiException;
use Brevo\Client\Model\GetAccount;
use Exception;
use GuzzleHttp\Client;

/**
 * Account Manager for Brevo Api.
 */
class AccountManager
{
    use ErrorLoggerTrait;

    /**
     * @var AccountApi
     */
    private AccountApi $accountApi;

    /**
     * @param Configuration $configurationManager
     */
    public function __construct(
        readonly Configuration $configurationManager
    ) {
        $this->accountApi = new AccountApi(
            new Client(),
            $configurationManager->getSdkConfig()
        );
    }

    /**
     * Read Account Information from Api.
     *
     * @return null|GetAccount
     */
    public function getAccount(): ?GetAccount
    {
        try {
            return $this->accountApi->getAccount();
        } catch (ApiException $ex) {
            return $this->catchError($ex);
        } catch (Exception $ex) {
            return $this->setError($ex->getMessage());
        }
    }
}
