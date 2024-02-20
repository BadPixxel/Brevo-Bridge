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

namespace BadPixxel\BrevoBridge\Interfaces;

use Doctrine\Common\Collections\Collection;

/**
 * Common Interface for Sms Aware Objects
 */
interface SmsAwareInterface
{
    /**
     * Get Doctrine Entity ID.
     */
    public function getId(): ?int;

    /**
     * Get Sms.
     */
    public function getSendSms(): Collection;

    /**
     * Check if User Has Stored Sms.
     */
    public function hasSendSms(): bool;
}
