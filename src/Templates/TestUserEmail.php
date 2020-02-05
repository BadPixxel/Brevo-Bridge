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
    const TEST_SUBJECT = "[BadPixxel][Test] Test d'envoi d'un message...";

    /**
     * @var string
     */
    const TEST_MSG = "Ceci est un message de test!\n"
            .'Merci de ne pas y répondre.';

    /**
     * Construct Minimal Email
     *
     * @param User $toUser
     */
    public function __construct(User $toUser)
    {
        parent::__construct($toUser);

        $this->email->setSubject(self::TEST_SUBJECT);
        $this->email->setTextContent(self::TEST_MSG);
    }

    /**
     * Create Email Instance in Demo Mode
     *
     * @param User $toUser
     *
     * @return TestUserEmail
     */
    protected static function getInstance(User $toUser): TestUserEmail
    {
        return new self($toUser);
    }

    /**
     * Create Email Instance in Demo Mode
     *
     * @param User $toUser
     *
     * @return TestUserEmail
     */
    protected static function getDemoInstance(User $toUser): TestUserEmail
    {
        return self::getInstance($toUser);
    }

//    /**
//     * {@inheritdoc}
//     */
//    public function sendDemo(User $user)
//    {
////        $this->smtpApi = $smtpApi;
//
//
//        $this->getSmtpManager();
//    }

    //put your code here
}
