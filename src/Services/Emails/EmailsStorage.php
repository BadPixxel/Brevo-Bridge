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

namespace BadPixxel\BrevoBridge\Services\Emails;

use BadPixxel\BrevoBridge\Entity\AbstractEmailStorage;
use BadPixxel\BrevoBridge\Helpers\EmailExtractor;
use BadPixxel\BrevoBridge\Repository\EmailRepository;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Model\CreateSmtpEmail;
use Brevo\Client\Model\SendSmtpEmail;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\UserBundle\Model\UserInterface as User;

/**
 * Manage Storage of User Emails in Database.
 */
class EmailsStorage
{
    public function __construct(
        private readonly Configuration  $config,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Find a User by Email
     */
    public function getUserByEmail(string $userEmail): ?User
    {
        $repository = $this->entityManager
            ->getRepository($this->config->getUserStorageClass())
        ;
        $user = $repository->findOneBy(array(
            "email" => $userEmail
        ));

        return ($user instanceof User) ? $user : null;
    }

    /**
     * Search for Send Email by Message ID
     */
    public function findByMessageId(string $messageId): ?AbstractEmailStorage
    {
        /** @var EmailRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->config->getEmailStorageClass());

        $storageEmail = $repository->findOneBy(array(
            "messageId" => $messageId
        ));

        return ($storageEmail instanceof AbstractEmailStorage) ? $storageEmail : null;
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
    public function filterAlreadySendUsers(array $toUsers, SendSmtpEmail $sendEmail, bool $demoMode): ?array
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
     */
    public function saveSendEmail(array $toUsers, SendSmtpEmail $sendEmail, CreateSmtpEmail $createEmail): self
    {
        $storageClass = $this->config->getEmailStorageClass();
        foreach ($toUsers as $toUser) {
            //==============================================================================
            // Check if User Exists in Db
            if (!($toUser instanceof User) || empty($toUser->getId())) {
                continue;
            }
            //==============================================================================
            // Check Class is SMS Storage Class
            if (!is_subclass_of($storageClass, AbstractEmailStorage::class)) {
                return $this;
            }
            //==============================================================================
            // Create & Persist Email Storage
            $storageEmail = $storageClass::fromApiResults($toUser, $sendEmail, $createEmail);
            $this->entityManager->persist($storageEmail);
        }
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Update this Email Events in Database
     */
    public function updateSendEmailEvents(AbstractEmailStorage $storageEmail, array $events): self
    {
        $storageEmail->setEvents($events);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Set this Email Events Refresh Errored in Database
     */
    public function updateSendEmailEventsErrored(AbstractEmailStorage $storageEmail): self
    {
        $storageEmail->setErrored();
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Update this Email Contents in Database
     */
    public function updateSendEmailContents(AbstractEmailStorage $storageEmail, string $uuid, string $contents): self
    {
        $storageEmail->setContents($uuid, $contents);
        $this->entityManager->flush();

        return $this;
    }

    /**
     * Check if this Email was Already Send to this User
     */
    private function isAlreadySend(User $toUser, SendSmtpEmail $email): bool
    {
        /** @var EmailRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->config->getEmailStorageClass())
        ;

        return !empty($repository->findByMd5($toUser, EmailExtractor::md5($email)));
    }
}
