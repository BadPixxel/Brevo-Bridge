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

use Exception;
use SendinBlue\Client\Api\AccountApi;
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
     * @param AccountApi $accountApi
     */
    public function __construct(AccountApi $accountApi)
    {
        $this->accountApi = $accountApi;
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
        } catch (Exception $ex) {
            $this->catchError($ex);

            return null;
        }
    }
}
