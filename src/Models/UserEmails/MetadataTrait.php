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

namespace BadPixxel\BrevoBridge\Models\UserEmails;

use Brevo\Client\Model\GetEmailEventReportEvents;
use Brevo\Client\Model\GetEmailEventReportEvents as Event;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * Emails Storage Metadata.
 */
trait MetadataTrait
{
    //==============================================================================
    // DEFINITION DE DONNEES
    //==============================================================================

    /**
     * Date the Email was Send.
     */
    #[ORM\Column(name: "send_at", type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $sendAt = null;

    /**
     * Date of First Opening.
     */
    #[ORM\Column(name: "open_at", type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $openAt = null;

    /**
     * Date of last Events Refresh.
     */
    #[ORM\Column(name: "refreshed_at", type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTime $refreshedAt = null;

    /**
     * Last Collected Events List
     *
     * @var null|GetEmailEventReportEvents[]
     */
    #[ORM\Column(name: "events", type: Types::ARRAY, nullable: true)]
    protected ?array $events = null;

    /**
     * @var array
     */
    private static array $eventSuccess = array(
        Event::EVENT_DELIVERED,
        Event::EVENT_OPENED,
        Event::EVENT_CLICKS,
    );

    /**
     * @var array
     */
    private static array $eventDanger = array(
        Event::EVENT_SPAM,
        Event::EVENT_INVALID,
        Event::EVENT_BLOCKED,
    );

    //==============================================================================
    // MAIN FUNCTIONS
    //==============================================================================

    /**
     * Check if Email Events are OutDated.
     *
     * @return bool
     */
    public function isEventOutdated(): bool
    {
        //==============================================================================
        // Was Refreshed within Last 12 Hour => Not OutDated
        if (null != $this->refreshedAt) {
            $ouDatedDate = new DateTime('-12 hour');
            if ($ouDatedDate < $this->refreshedAt) {
                return false;
            }
        }
        //==============================================================================
        // Already Marked as Errored => Not OutDated
        if ($this->isErrored()) {
            return false;
        }
        //==============================================================================
        // No Send Date => Should Not Happen => Not OutDated
        if (null == $this->sendAt) {
            return false;
        }
        //==============================================================================
        // Send more than 4 Weeks Agp => OutDated but no use to Refresh
        $deprecatedDate = new DateTime('-4 week');
        if ($deprecatedDate > $this->sendAt) {
            return false;
        }

        return true;
    }

    /**
     * @param array $events
     *
     * @throws Exception
     *
     * @return $this
     */
    public function setEvents(array $events): static
    {
        $this->events = $events;
        //==============================================================================
        // Update RefreshedAt
        $this->refreshedAt = new DateTime();
        //==============================================================================
        // Detect Email OpenedAt
        if (is_null($this->openAt)) {
            /** @var GetEmailEventReportEvents $event */
            foreach ($events as $event) {
                if (Event::EVENT_OPENED == $event->getEvent()) {
                    $this->setOpenAt(new DateTime($event->getDate()));

                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Mark this Email was Errored during Refresh
     *
     * @return $this
     */
    public function setErrored(): static
    {
        $this->events = array(new Event(array(
            "date" => new DateTime(),
            "event" => Event::EVENT_INVALID,
            "reason" => "Unable to update Email Metadatas",
        )));

        return $this;
    }

    /**
     * Get Bootstrap badge Class for Event.
     *
     * @param string $type
     *
     * @return string
     */
    public function getEventBadgeClass(string $type): string
    {
        if (in_array($type, self::$eventSuccess, true)) {
            return 'success';
        }
        if (in_array($type, self::$eventDanger, true)) {
            return 'danger';
        }

        return 'primary';
    }

    /**
     * Check if Email was Delivered.
     *
     * @return bool
     */
    public function isDelivered(): bool
    {
        return $this->hasEvent(Event::EVENT_DELIVERED);
    }

    /**
     * Check if Email was Open.
     *
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->hasEvent(Event::EVENT_OPENED);
    }

    /**
     * Check if Email was Blocked.
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->hasEvent(Event::EVENT_BLOCKED);
    }

    /**
     * Check if Email is in Error.
     *
     * @return bool
     */
    public function isErrored(): bool
    {
        foreach (self::$eventDanger as $eventType) {
            if ($this->hasEvent($eventType)) {
                return true;
            }
        }

        return false;
    }

    //==============================================================================
    // COMMON GETTERS & SETTERS
    //==============================================================================

    /**
     * Get sendAt.
     *
     * @return null|DateTime
     */
    public function getSendAt(): ?DateTime
    {
        return $this->sendAt;
    }

    /**
     * Get openAt.
     *
     * @return null|DateTime
     */
    public function getOpenAt(): ?DateTime
    {
        return $this->openAt;
    }

    /**
     * Get refreshedAt.
     *
     * @return null|DateTime
     */
    public function getRefreshedAt(): ?DateTime
    {
        return $this->refreshedAt;
    }

    /**
     * @return null|array
     */
    public function getEvents(): ?array
    {
        return $this->events;
    }

    //==============================================================================
    // PRIVATE FUNCTIONS
    //==============================================================================

    /**
     * Check if Email has an Event Type
     *
     * @param string $eventType
     *
     * @return bool
     */
    public function hasEvent(string $eventType): bool
    {
        //==============================================================================
        // Safety Check
        if (!is_array($this->events)) {
            return false;
        }
        //==============================================================================
        // Walk on Email Events
        foreach ($this->events as $event) {
            if ($eventType == $event->getEvent()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set sendAt.
     */
    protected function setSendAt(DateTime $sendAt = null): static
    {
        $this->sendAt = $sendAt ? $sendAt : (new DateTime());

        return $this;
    }

    /**
     * Set openAt.
     */
    protected function setOpenAt(DateTime $openAt = null): static
    {
        $this->openAt = $openAt ? $openAt : (new DateTime());

        return $this;
    }
}
