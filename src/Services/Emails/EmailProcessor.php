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

namespace BadPixxel\BrevoBridge\Services\Emails;

use BadPixxel\BrevoBridge\Dictionary\ServiceTags;
use BadPixxel\BrevoBridge\Interfaces\EmailProcessorInterface;
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * Execute All Processors for an Email before Sending
 */
class EmailProcessor
{
    /**
     * @param EmailProcessorInterface[] $processors
     */
    public function __construct(
        #[TaggedIterator(ServiceTags::EMAIL_PROCESSOR)]
        private readonly iterable $processors
    ) {
    }

    /**
     * Execute all registered processors for this Email before Sending
     */
    public function process(AbstractEmail $email): AbstractEmail
    {
        foreach ($this->processors as $processor) {
            if ($processor->supports(get_class($email))) {
                $processor->process($email);
            }
        }

        return $email;
    }
}
