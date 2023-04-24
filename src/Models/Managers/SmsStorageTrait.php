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

namespace BadPixxel\SendinblueBridge\Models\Managers;

use BadPixxel\SendinblueBridge\Helpers\SmsExtractor;
use BadPixxel\SendinblueBridge\Repository\SmsRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use SendinBlue\Client\Model\SendSms;
use SendinBlue\Client\Model\SendTransacSms;
use Sonata\UserBundle\Model\UserInterface as User;

/**
 * Manage Storage of User Sms in Database.
 */
trait SmsStorageTrait
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * Find a User by Email
     *
     * @param string $userEmail
     *
     * @return null|User
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
     * Check if this Sms was Already Send to this User
     *
     * @param User           $user
     * @param SendTransacSms $sms
     * @param bool           $demoMode
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function isAlreadySend(User $user, SendTransacSms $sms, bool $demoMode): bool
    {
        //==============================================================================
        // DEMO MODE => Allow Multiple Sending to User
        if ($demoMode) {
            return false;
        }

        /** @var SmsRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->config->getSmsStorageClass());

        return !empty($repository->findByMd5($user, SmsExtractor::md5($sms)));
    }

    /**
     * Save this Sms in Database
     *
     * @param User           $user
     * @param SendTransacSms $sendSms
     * @param SendSms        $createSms
     *
     * @return self
     */
    protected function saveSendSms(User $user, SendTransacSms $sendSms, SendSms $createSms): self
    {
        $storageClass = $this->config->getSmsStorageClass();
        //==============================================================================
        // Check if User Exists in Db
        if (!($user instanceof User) || empty($user->getId())) {
            return $this;
        }
        //==============================================================================
        // Create & Persist Email Storage
        $storageEmail = $storageClass::fromApiResults($user, $sendSms, $createSms);
        $this->entityManager->persist($storageEmail);
        $this->entityManager->flush();

        return $this;
    }
}
