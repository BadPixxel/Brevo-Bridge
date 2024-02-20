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

use BadPixxel\BrevoBridge\Helpers\EmailExtractor;
use BadPixxel\BrevoBridge\Models\UserEmails;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\SendSmtpEmail;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Model\UserInterface as User;

/**
 * Base Class for User Email Historic Storage.
 */
#[ORM\MappedSuperclass]
abstract class AbstractEmailStorage
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
     */
    public static function fromApiResults(
        User $toUser,
        SendSmtpEmail $sendEmail,
        CreateSmtpEmail $createEmail
    ): AbstractEmailStorage {
        $storage = new static();
        $storage
            ->setSendAt()
            ->setUser($toUser)
            ->setEmail((string) $toUser->getEmailCanonical())
            ->setSubject($sendEmail->getSubject())
            ->setHtmlContent($sendEmail->getHtmlContent())
            ->setTextContent($sendEmail->getTextContent())
            ->setTemplateId($sendEmail->getTemplateId())
            ->setParameters((array) $sendEmail->getParams())
            ->setMd5(EmailExtractor::md5($sendEmail))
            ->setMessageId($createEmail->getMessageId())
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
