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

namespace BadPixxel\SendinblueBridge\Interfaces;

use Doctrine\Common\Collections\Collection;

/**
 * Common Interface for Emails Aware Objects
 */
interface EmailsAwareInterface
{
    /**
     * Get Emails.
     *
     * @return Collection
     */
    public function getEmails();
}
