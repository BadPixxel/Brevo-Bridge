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

use FOS\UserBundle\Model\UserInterface as User;
use FOS\UserBundle\Model\UserManagerInterface;

/**
 * Helper Functions to find Users by Email
 */
trait UserFinderTrait
{
    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * Find an User by Email
     *
     * @param string $userEmail
     *
     * @return null|User
     */
    public function getUserByEmail(string $userEmail): ?User
    {
        return $this->userManager->findUserByEmail($userEmail);
    }

    /**
     * @param UserManagerInterface $userManager
     *
     * @return self
     */
    protected function setUserManager(UserManagerInterface $userManager): self
    {
        $this->userManager = $userManager;

        return $this;
    }
}
