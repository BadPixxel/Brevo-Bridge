<?php

namespace BadPixxel\BrevoBridge\Models\Sms;

use Symfony\Component\Security\Core\User\UserInterface;

trait SendToUserTrait
{
    /**
     * Current User.
     */
    protected UserInterface $user;

    /**
     * Add User to a Sms List.
     */
    public function setToUser(UserInterface $user): static
    {
        $this->user = $user;
        if (method_exists($user, "getPhone")) {
            $this->sms->setRecipient($user->getPhone());
        }

        return $this;
    }
}