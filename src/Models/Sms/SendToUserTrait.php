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
