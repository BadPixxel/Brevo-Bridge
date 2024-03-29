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

namespace BadPixxel\BrevoBridge;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * A Small Bundle to Manage Sending User Email, Events & Sms via Sendinblue Transactional API.
 *
 * @property null|ContainerInterface $container
 */
class BrevoBridgeBundle extends Bundle
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if (!$this->container) {
            return;
        }
        //==============================================================================
        // Force Loading of SendInBlue Smtp Service
        $this->container->get(Services\Emails\EmailsManager::class);
        //==============================================================================
        // Force Loading of SendInBlue Sms Service
        $this->container->get(Services\Sms\SmsManager::class);
        //==============================================================================
        // Force Loading of SendInBlue Events Service
        $this->container->get(Services\Events\EventManager::class);
    }
}
