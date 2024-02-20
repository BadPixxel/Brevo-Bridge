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

namespace BadPixxel\BrevoBridge\Tests\Bundle\Processors\Sms;

use BadPixxel\BrevoBridge\Models\AbstractSms;
use BadPixxel\BrevoBridge\Models\AbstractSmsProcessor;
use BadPixxel\BrevoBridge\Tests\Bundle\Interfaces\DummySubjectAwareSmsInterface;

/**
 * Just a Basic Processor to Complete Tests Sms with Dummy Subject
 */
class DummySubjectProcessor extends AbstractSmsProcessor
{
    /**
     * @inheritDoc
     */
    public function supports(string $smsClass): bool
    {
        return is_subclass_of($smsClass, DummySubjectAwareSmsInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function process(AbstractSms $sms): void
    {
        if (!is_subclass_of($sms, DummySubjectAwareSmsInterface::class)) {
            return;
        }
        //==============================================================================
        // Setup Email Dummy Urls
        $sms->mergeParams(array(
            'subject' => sprintf("%s is a random Processor Subject", uniqid()),
        ));
    }
}
