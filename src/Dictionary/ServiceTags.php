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

namespace BadPixxel\BrevoBridge\Dictionary;

/**
 * Service Tags for Using Brevo Autoconfiguration
 */
class ServiceTags
{
    /**
     * Brevo Bridge Emails
     */
    const EMAIL = "badpixxel.brevo.bridge.email";

    /**
     * Brevo Bridge Email Processor
     */
    const EMAIL_PROCESSOR = "badpixxel.brevo.bridge.email.processor";

    /**
     * Brevo Bridge Sms
     */
    const SMS = "badpixxel.brevo.bridge.sms";

    const SMS_PROCESSOR = "badpixxel.brevo.bridge.sms.processor";

    /**
     * Brevo Bridge Event
     */
    const EVENT = "badpixxel.brevo.bridge.event";

}
