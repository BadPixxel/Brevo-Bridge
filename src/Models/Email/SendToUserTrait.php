<?php

namespace BadPixxel\BrevoBridge\Models\Email;

use Brevo\Client\Model;
use Symfony\Component\Security\Core\User\UserInterface as User;

/**
 * Manage Target Users for Abstract Emails
 */
trait SendToUserTrait
{
    /**
     * Current User.
     *
     * @var User[]
     */
    protected array $toUsers;

    /**
     * Get To Users List.
     */
    final public function getToUsers(): array
    {
        return $this->toUsers;
    }

    /**
     * Add Users to an Emails List.
     *
     * @param User|User[] $toUsers Target User or Array of Target Users
     *
     * @return $this
     */
    final public function setToUsers(array|User $toUsers): static
    {
        //==============================================================================
        // Ensure we have a List of Users
        if (!is_array($toUsers)) {
            $toUsers = array($toUsers);
        }
        //==============================================================================
        // Verify & Store List of Target Users
        $this->toUsers = array();
        foreach ($toUsers as $toUser) {
            if (!$toUser instanceof User) {
                continue;
            }
            $this->addToUser($toUser);
            $this->toUsers[] = $toUser;
        }

        return $this;
    }

    /**
     * Add User to Email To.
     */
    final protected function addToUser(User $user): static
    {
        $this->email->setTo(array_merge(
            $this->email->getTo() ?: array(),
            array(
                new Model\SendSmtpEmailTo(self::toUserArgs($user))
            )
        ));

        return $this;
    }

    /**
     * Add User to Email Cc.
     */
    final protected function addCcUser(User $user): static
    {
        $this->email->setCc(array_merge(
            $this->email->getCc() ?: array(),
            array(
                new Model\SendSmtpEmailCc(self::toUserArgs($user))
            )
        ));

        return $this;
    }

    /**
     * Add User to Email Bcc.
     */
    protected function addBccUser(User $user): static
    {
        $this->email->setBcc(array_merge(
            $this->email->getBcc() ?: array(),
            array(
                new Model\SendSmtpEmailBcc(self::toUserArgs($user))
            )
        ));

        return $this;
    }

    /**
     * Extract User Information for Building To User.
     */
    private static function toUserArgs(User $toUser): array
    {
        //==============================================================================
        // Extract User Name
        $name = method_exists($toUser, "__toString")
            ? $toUser->__toString()
            : $toUser->getUserIdentifier()
        ;
        //==============================================================================
        // Extract User Email
        $email = method_exists($toUser, "getEmailCanonical")
            ? $toUser->getEmailCanonical()
            : $toUser->getUserIdentifier()
        ;
        //==============================================================================
        // Create To User Array
        return array('name' => $name, 'email' => $email);
    }
}