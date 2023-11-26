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

namespace BadPixxel\BrevoBridge\Entity;

use BadPixxel\BrevoBridge\Helpers\SmsExtractor;
use BadPixxel\BrevoBridge\Models\UserEmails;
use Brevo\Client\Model\SendSms;
use Brevo\Client\Model\SendTransacSms;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Model\UserInterface as User;

/**
 * Base Class for User Sms Historic Storage.
 */
#[ORM\MappedSuperclass]
abstract class AbstractSmsStorage
{
    use UserEmails\ContentsTrait;
    use UserEmails\MetadataTrait;

    //==============================================================================
    // DATA STORAGE DEFINITION
    //==============================================================================

    /**
     * @var User
     */
    protected User $user;

    /**
     * Class Constructor
     */
    final public function __construct()
    {
    }

    /**
     * Implement toString Magic Method
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getSubject();
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
     */
    public static function fromApiResults(User $user, SendTransacSms $sendSms, SendSms $createSms): AbstractSmsStorage
    {
        $storage = new static();
        $storage
            ->setSendAt()
            ->setUser($user)
            ->setEmail((string) $user->getEmailCanonical())
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
     * Get Doctrine Entity ID.
     */
    abstract public function getId(): ?int;

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
