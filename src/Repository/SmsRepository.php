<?php

/*
 *  Copyright (C) 2021 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Repository;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Model\UserInterface as User;

/**
 * Users Send Sms Repository.
 */
class SmsRepository extends EntityRepository
{
    /**
     * Search for Sms with Similar User & Md5 in Database.
     *
     * @param User   $user
     * @param string $md5
     *
     * @return array
     */
    public function findByMd5(User $user, string $md5): array
    {
        return $this->findBy(array(
            'user' => $user,
            'md5' => $md5,
        ));
    }
}