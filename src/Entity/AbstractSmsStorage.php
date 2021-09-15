<?php

/*
 *  Copyright (C) 2021 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Entity;

use BadPixxel\SendinblueBridge\Helpers\SmsExtractor;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface as User;
use SendinBlue\Client\Model\SendSms;
use SendinBlue\Client\Model\SendTransacSms;

/**
 * Base Class for User Sms Historic Storage.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractSmsStorage
{
    use \BadPixxel\SendinblueBridge\Models\UserEmails\ContentsTrait;
    use \BadPixxel\SendinblueBridge\Models\UserEmails\MetadataTrait;

    //==============================================================================
    // DATA STORAGE DEFINITION
    //==============================================================================

    /**
     * @var User
     */
    protected $user;

    /**
     * Class Constructor
     */
    final public function __construct()
    {
    }

    //==============================================================================
    // MAIN FUNCTIONS
    //==============================================================================

    /**
     * Create Storage Class from Api Results
     *
     * @param User           $user
     * @param SendTransacSms $sendSms
     * @param SendSms        $createSms
     *
     * @return self
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function fromApiResults(User $user, SendTransacSms $sendSms, SendSms $createSms)
    {
        $storage = new static();
        $storage
            ->setSendAt()
            ->setUser($user)
            ->setEmail($user->getEmailCanonical())
            ->setSubject($sendSms->getRecipient())
            ->setTextContent($sendSms->getContent())
            ->setMd5(SmsExtractor::md5($sendSms))
            ->setMessageId((string) $createSms->getMessageId())
            ->setUuid($createSms->getReference())
        ;

        return $storage;
    }

    //==============================================================================
    // GENERIC GETTERS & SETTERS
    //==============================================================================

    /**
     * Get Doctrine Entity ID. Must be Overriden by Parent Class.
     *
     * @return int
     */
    public function getId()
    {
        return 0;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    protected function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
