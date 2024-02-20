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

namespace BadPixxel\BrevoBridge\Services\Sms;

use BadPixxel\BrevoBridge\Dictionary\ServiceTags;
use BadPixxel\BrevoBridge\Interfaces\SmsProcessorInterface;
use BadPixxel\BrevoBridge\Models\AbstractSms;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * Execute All Processors for a Sms before Sending
 */
class SmsProcessor
{
    /**
     * @param SmsProcessorInterface[] $processors
     */
    public function __construct(
        #[TaggedIterator(ServiceTags::SMS_PROCESSOR)]
        private readonly iterable $processors
    ) {
    }

    /**
     * Execute all registered processors for this Email before Sending
     */
    public function process(AbstractSms $sms): AbstractSms
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports(get_class($sms))) {
                $processor->process($sms);
            }
        }

        return $sms;
    }
}
