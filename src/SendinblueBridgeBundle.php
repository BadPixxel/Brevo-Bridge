<?php

/*
 * This file is part of Immo-Pop Website Project.
 *
 * Copyright (C) Immo-Pop SAS <www.immo-pop.com>
 * All rights reserved.
 *
 * NOTE: All information contained herein is, and remains the property of Splash
 * Sync and its suppliers, if any.  The intellectual and technical concepts
 * contained herein are proprietary to Splash Sync and its suppliers, and are
 * protected by trade secret or copyright law. Dissemination of this information
 * or reproduction of this material is strictly forbidden unless prior written
 * permission is obtained from Splash Sync.
 *
 * @author Bernard Paquier <contact@splashsync.com>
 * @author Yohann Bernard <contact@immo-pop.com>
 */

namespace BadPixxel\SendinblueBridge;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * A Small Bundle to Manage Sending User Email via Sendinblue Transactionnal API.
 */
class SendinblueBridgeBundle extends Bundle
{
    
    public function boot() {
        //==============================================================================
        // Force Loading of SendInBlue Smtp Service  
        $this->container->get('badpixxel.sendinblue.smtp');
    }
}
