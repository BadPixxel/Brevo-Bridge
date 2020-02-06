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

namespace BadPixxel\SendinblueBridge\Templates;

use BadPixxel\SendinblueBridge\Models\AbstractEmail;
use FOS\UserBundle\Model\UserInterface as User;

/**
 * Just Send a Basic Email tu User
 */
class TestUserEmail extends AbstractEmail
{
    /**
     * @var string
     */
    const TEST_SUBJECT = "[BadPixxel] Sendinblue Api Validation...";

    /**
     * @var string
     */
    const TEST_MSG = "This is just a dummy test message!";

    /**
     * Construct Minimal Email
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     */
    public function __construct($toUsers)
    {
        parent::__construct($toUsers);

        $this->email->setSubject(self::TEST_SUBJECT);
        $this->email->setTextContent(self::TEST_MSG);
    }

    /**
     * Create Email Instance in Demo Mode
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return TestUserEmail
     */
    protected static function getInstance($toUsers): TestUserEmail
    {
        return new self($toUsers);
    }

    /**
     * Create Email Instance in Demo Mode
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return TestUserEmail
     */
    protected static function getDemoInstance($toUsers): TestUserEmail
    {
        return self::getInstance($toUsers);
    }
}
