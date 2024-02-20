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

use BadPixxel\BrevoBridge\Models\AbstractEmail;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Manage Links between Users & Emails.
 */
trait EmailsTrait
{
    //==============================================================================
    // DATA DEFINITIONS
    //==============================================================================

    /**
     * @var Collection<AbstractEmail>
     */
    protected Collection $emails;

    //==============================================================================
    // GENERIC GETTERS & SETTERS
    //==============================================================================

    /**
     * Get Emails.
     *
     * @return Collection<AbstractEmail>
     */
    public function getEmails(): Collection
    {
        return $this->emails;
    }

    /**
     * Set Emails => NO EFFECT.
     *
     * @return self
     */
    public function setEmails(): self
    {
        return $this;
    }

    /**
     * Check if User Has Stored Emails.
     *
     * @return bool
     */
    public function hasEmails(): bool
    {
        //==============================================================================
        // New Subject
        if (empty($this->getId())) {
            return false;
        }
        //==============================================================================
        // Check Emails Collection Status
        if (!($this->emails instanceof Collection) || $this->emails->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Init Emails Collection.
     *
     * @return self
     */
    protected function initEmails(): self
    {
        $this->emails = new ArrayCollection();

        return $this;
    }
}
