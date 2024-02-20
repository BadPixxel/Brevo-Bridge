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

namespace BadPixxel\BrevoBridge\Services\Sms;

use BadPixxel\BrevoBridge\Entity\AbstractSmsStorage;
use BadPixxel\BrevoBridge\Helpers\SmsExtractor;
use BadPixxel\BrevoBridge\Models\Managers;
use BadPixxel\BrevoBridge\Repository\SmsRepository;
use BadPixxel\BrevoBridge\Services\ConfigurationManager as Configuration;
use Brevo\Client\Model\SendSms;
use Brevo\Client\Model\SendTransacSms;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\UserBundle\Model\UserInterface as User;

/**
 * Manage Storage of User Sms in Database.
 */
class SmsStorage
{
    use Managers\ErrorLoggerTrait;

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
     * Check if this Sms was Already Send to this User
     */
    public function isAlreadySend(User $user, SendTransacSms $sms, bool $demoMode): bool
    {
        //==============================================================================
        // DEMO MODE => Allow Multiple Sending to User
        if ($demoMode) {
            return false;
        }

        /** @var SmsRepository $repository */
        $repository = $this->entityManager
            ->getRepository($this->config->getSmsStorageClass())
        ;

        return !empty($repository->findByMd5($user, SmsExtractor::md5($sms)));
    }

    /**
     * Save this Sms in Database
     */
    public function saveSendSms(User $user, SendTransacSms $sendSms, SendSms $createSms): static
    {
        $storageClass = $this->config->getSmsStorageClass();
        //==============================================================================
        // Check if User Exists in Db
        if (empty($user->getId())) {
            return $this;
        }
        //==============================================================================
        // Check Class is SMS Storage Class
        if (!is_subclass_of($storageClass, AbstractSmsStorage::class)) {
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
