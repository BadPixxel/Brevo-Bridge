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

namespace BadPixxel\SendinblueBridge\Models\UserEmails;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use SendinBlue\Client\Model\GetEmailEventReportEvents as Event;

/**
 * Emails Storage Metadata.
 */
trait MetadataTrait
{
    /**
     * @var array
     */
    private static $eventSuccess = array(
        Event::EVENT_DELIVERED,
        Event::EVENT_OPENED,
        Event::EVENT_CLICKS,
    );

    /**
     * @var array
     */
    private static $eventDanger = array(
        Event::EVENT_SPAM,
        Event::EVENT_INVALID,
        Event::EVENT_BLOCKED,
        //        Event::EVENT_UNSUBSCRIBED
    );

    //==============================================================================
    // DEFINITION DE DONNEES
    //==============================================================================

    /**
     * Date the Email was Send.
     *
     * @var null|DateTime
     *
     * @ORM\Column(name="send_at", type="datetime", nullable=true)
     */
    private $sendAt;

    /**
     * Date of First Opening.
     *
     * @var null|DateTime
     *
     * @ORM\Column(name="open_at", type="datetime", nullable=true)
     */
    private $openAt;

    /**
     * Date of last Events Refresh.
     *
     * @var null|DateTime
     *
     * @ORM\Column(name="refreshed_at", type="datetime", nullable=true)
     */
    private $refreshedAt;

    /**
     * @var null|array
     *
     * @ORM\Column(name="events", type="array", nullable=true)
     */
    private $events;

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
        if (null == $this->refreshedAt) {
            return true;
        }
        $oudatedDate = new DateTime('-1 days');

        return $oudatedDate > $this->refreshedAt;
    }

    /**
     * @param array $events
     *
     * @return $this
     */
    public function setEvents(array $events): self
    {
        $this->events = $events;
        //==============================================================================
        // Update RefreshedAt
        $this->refreshedAt = new DateTime();
        //==============================================================================
        // Detect Email OpenedAt
        if (is_null($this->openAt)) {
            foreach ($events as $event) {
                if (Event::EVENT_OPENED == $event->getEvent()) {
                    $this->setOpenAt($event->getDate());

                    break;
                }
            }
        }

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

    /**
     * Set sendAt.
     *
     * @param DateTime $sendAt
     *
     * @return $this
     */
    protected function setSendAt(DateTime $sendAt = null): self
    {
        $this->sendAt = $sendAt ? $sendAt : (new DateTime());

        return $this;
    }

    /**
     * Set openAt.
     *
     * @param DateTime $openAt
     *
     * @return $this
     */
    protected function setOpenAt(DateTime $openAt = null): self
    {
        $this->openAt = $openAt ? $openAt : (new DateTime());

        return $this;
    }
}
