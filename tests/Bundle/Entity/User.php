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

namespace BadPixxel\BrevoBridge\Tests\Bundle\Entity;

use BadPixxel\BrevoBridge\Interfaces\EmailsAwareInterface;
use BadPixxel\BrevoBridge\Interfaces\SmsAwareInterface;
use BadPixxel\BrevoBridge\Models\User\EmailsTrait;
use BadPixxel\BrevoBridge\Models\User\SmsTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Entity\BaseUser;

/**
 * Base Class for Sonata User.
 */
#[ORM\Entity]
#[ORM\Table(name: 'user__user')]
class User extends BaseUser implements EmailsAwareInterface, SmsAwareInterface
{
    use EmailsTrait;
    use SmsTrait;

    /**
     * @var null|int
     */
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected $id;

    //==============================================================================
    // DATA DEFINITIONS
    //==============================================================================

    /**
     * Target Phone
     */
    #[ORM\Column(name: "phone", type: Types::STRING, length: 255, nullable: true)]
    protected ?string $phone = null;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Email::class)]
    #[ORM\OrderBy(array("sendAt" => "DESC"))]
    protected Collection $emails;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Sms::class)]
    #[ORM\OrderBy(array("sendAt" => "DESC"))]
    protected Collection $sendSms;

    public function getId(): ?int
    {
        return $this->id;
    }

    //==============================================================================
    // GENERIC GETTERS & SETTERS
    //==============================================================================

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone ?? "+336060606";
    }
}
