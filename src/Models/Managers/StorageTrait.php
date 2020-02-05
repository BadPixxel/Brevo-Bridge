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

use BadPixxel\SendinblueBridge\Entity\AbstractEmailStorage as EmailStorage;
use BadPixxel\SendinblueBridge\Helpers\EmailExtractor;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use FOS\UserBundle\Model\UserInterface as User;
use SendinBlue\Client\Model\CreateSmtpEmail;
use SendinBlue\Client\Model\SendSmtpEmail;

/**
 * Manage Storage of User Emails in Database.
 */
trait StorageTrait
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param UserManagerInterface $userManager
     *
     * @return self
     */
    protected function setupStorage(EntityManager $manager): self
    {
        $this->entityManager = $manager;

        return $this;
    }

    /**
     * Check if this Email was Already Send to this User
     *
     * @return self
     */
    protected function isAlreadySend(User $toUser, SendSmtpEmail $email): bool
    {
        $similarEmails = $this->entityManager
            ->getRepository($this->config->getEmailStorageClass())
            ->findByMd5($toUser, EmailExtractor::md5($email));

        return !empty($similarEmails);
    }

    /**
     * Save this Email in Database
     *
     * @return self
     */
    protected function saveSendEmail(User $toUser, SendSmtpEmail $sendEmail, CreateSmtpEmail $createEmail): self
    {
        $storageClass = $this->config->getEmailStorageClass();

        $storageEmail = $storageClass::fromApiResults($toUser, $sendEmail, $createEmail);

        $this->entityManager->persist($storageEmail);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Update this Email Events in Database
     *
     * @return self
     */
    protected function updateSendEmailEvents(EmailStorage $storageEmail, array $events): self
    {
        $storageEmail->setEvents($events);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Update this Email Contyents in Database
     *
     * @return self
     */
    protected function updateSendEmailContents(EmailStorage $storageEmail, string $uuid, string $contents): self
    {
        $storageEmail->setContents($uuid, $contents);
        $this->entityManager->flush();

        return $this;
    }
}
