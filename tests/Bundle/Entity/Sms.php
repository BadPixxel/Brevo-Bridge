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

use BadPixxel\BrevoBridge\Entity\AbstractSmsStorage;
use BadPixxel\BrevoBridge\Models\Gdpr\GdprEntityTrait;
use BadPixxel\Paddock\System\MySql\Models\GdprRemovableInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\UserBundle\Model\UserInterface as User;

#[ORM\Entity]
#[ORM\Table(name: 'user__sms')]
class Sms extends AbstractSmsStorage implements GdprRemovableInterface
{
    use GdprEntityTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected ?int $id;

    /**
     * @inheritdoc
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "sms")]
    protected User $user;
    //==============================================================================
    // GENERIC GETTERS & SETTERS
    //==============================================================================

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
