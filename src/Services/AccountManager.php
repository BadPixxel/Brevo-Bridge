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

use BadPixxel\SendinblueBridge\Services\ConfigurationManager as Configuration;
use Exception;
use GuzzleHttp\Client;
use SendinBlue\Client\Api\AccountApi;
use SendinBlue\Client\ApiException;
use SendinBlue\Client\Model\GetAccount;

/**
 * Account Manager for SendingBlue Api.
 */
class AccountManager
{
    use \BadPixxel\SendinblueBridge\Models\Managers\ErrorLoggerTrait;

    /**
     * @var AccountApi
     */
    private $accountApi;

    /**
     * @param ConfigurationManager $configurationManager
     */
    public function __construct(Configuration $configurationManager)
    {
        $this->accountApi = new AccountApi(
            new Client(),
            $configurationManager->getSdkConfig()
        );
    }

    /**
     * Read Account Infromations from Api.
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
