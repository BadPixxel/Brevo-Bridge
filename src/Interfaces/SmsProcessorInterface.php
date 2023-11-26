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

use BadPixxel\BrevoBridge\Models\AbstractSms;

/**
 * Interface for all Brevo Sms Processor
 */
interface SmsProcessorInterface
{
    /**
     * Check if this Processor Support this Sms Class
     *
     * @param class-string $smsClass
     *
     * @return bool
     */
    public function supports(string $smsClass): bool;

    /**
     * Process this Sms before Sending
     */
    public function process(AbstractSms $sms): void;
}
