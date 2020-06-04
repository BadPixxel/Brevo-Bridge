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
use BadPixxel\SendinblueBridge\Repository\EmailRepository;
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
     * Setup Entity Manager for Storage
     *
     * @param EntityManager $manager
     *
     * @return self
     */
    protected function setupStorage(EntityManager $manager): self
    {
        $this->entityManager = $manager;

        return $this;
    }

    /**
     * Filter Target Users if Email was Already Send.
     *
     * @param User[]        $toUsers
     * @param SendSmtpEmail $sendEmail
     * @param bool          $demoMode
     *
     * @return null|User[]
     */
    protected function filterAlreadySendUsers(array $toUsers, SendSmtpEmail $sendEmail, bool $demoMode): ?array
    {
        //==============================================================================
        // DEMO MODE => Allow Multiple Sending to User
        if ($demoMode) {
            return $toUsers;
        }
        //==============================================================================
        // NORMAL MODE => Filter Sending to User
        foreach ($toUsers as $index => $toUser) {
            //==============================================================================
            // Check if THIS Email was Already Send
            if (!$this->isAlreadySend($toUser, $sendEmail)) {
                continue;
            }
            //==============================================================================
            // Remove User from To Users
            unset($toUsers[$index]);
            //==============================================================================
            // Remove User from To Emails
            $emailTo = $sendEmail->getTo();
            /** @var \ArrayAccess  $emailUser */
            foreach ($emailTo as $emailIndex => $emailUser) {
                if ($emailUser['email'] == $toUser->getEmailCanonical()) {
                    unset($emailTo[$emailIndex]);
                }
            }
            $sendEmail->setTo($emailTo);
        }

        return empty($toUsers) ? null : $toUsers;
    }

    /**
     * Save this Email in Database
     *
     * @param array           $toUsers
     * @param SendSmtpEmail   $sendEmail
     * @param CreateSmtpEmail $createEmail
     *
     * @return self
     */
    protected function saveSendEmail(array $toUsers, SendSmtpEmail $sendEmail, CreateSmtpEmail $createEmail): self
    {
        $storageClass = $this->config->getEmailStorageClass();
        foreach ($toUsers as $toUser) {
            $storageEmail = $storageClass::fromApiResults($toUser, $sendEmail, $createEmail);
            $this->entityManager->persist($storageEmail);
        }
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Update this Email Events in Database
     *
     * @param EmailStorage $storageEmail
     * @param array        $events
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
     * Set this Email Events Refresh Errored in Database
     *
     * @param EmailStorage $storageEmail
     *
     * @return self
     */
    protected function updateSendEmailEventsErrored(EmailStorage $storageEmail): self
    {
        $storageEmail->setErrored();
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Update this Email Contents in Database
     *
     * @param EmailStorage $storageEmail
     * @param string       $uuid
     * @param string       $contents
     *
     * @return self
     */
    protected function updateSendEmailContents(EmailStorage $storageEmail, string $uuid, string $contents): self
    {
        $storageEmail->setContents($uuid, $contents);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Check if this Email was Already Send to this User
     *
     * @param User          $toUser
     * @param SendSmtpEmail $email
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function isAlreadySend(User $toUser, SendSmtpEmail $email): bool
    {
        /** @var EmailRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->config->getEmailStorageClass());

        return !empty($repository->findByMd5($toUser, EmailExtractor::md5($email)));
    }
}
