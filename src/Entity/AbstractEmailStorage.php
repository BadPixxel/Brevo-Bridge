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

namespace BadPixxel\SendinblueBridge\Entity;

use BadPixxel\SendinblueBridge\Helpers\EmailExtractor;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\UserInterface as User;
use SendinBlue\Client\Model\CreateSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmail;

/**
 * Base Class for User Email Historics Storage.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractEmailStorage
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
     * @return self
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function fromApiResults(User $toUser, SendSmtpEmail $sendEmail, CreateSmtpEmail $createEmail)
    {
        $storage = new static();
        $storage
            ->setSendAt()
            ->setUser($toUser)
            ->setEmail($toUser->getEmailCanonical())
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
