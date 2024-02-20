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

namespace BadPixxel\BrevoBridge\Models\User;

use BadPixxel\BrevoBridge\Models\AbstractSms;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Manage Links between Users & Sms.
 */
trait SmsTrait
{
    //==============================================================================
    // DATA DEFINITIONS
    //==============================================================================

    /**
     * @var Collection<AbstractSms>
     */
    protected Collection $sendSms;

    //==============================================================================
    // GENERIC GETTERS & SETTERS
    //==============================================================================

    /**
     * Get Sms.
     *
     * @return Collection<AbstractSms>
     */
    public function getSendSms(): Collection
    {
        return $this->sendSms;
    }

    /**
     * Set Sms => NO EFFECT.
     *
     * @return self
     */
    public function setSendSms(): self
    {
        return $this;
    }

    /**
     * Check if User Has Stored Sms.
     *
     * @return bool
     */
    public function hasSendSms(): bool
    {
        //==============================================================================
        // New Subject
        if (empty($this->getId())) {
            return false;
        }
        //==============================================================================
        // Check Emails Collection Status
        if (!($this->sendSms instanceof Collection) || $this->sendSms->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Init Sms Collection.
     *
     * @return self
     */
    protected function initSms(): self
    {
        $this->sendSms = new ArrayCollection();

        return $this;
    }
}
