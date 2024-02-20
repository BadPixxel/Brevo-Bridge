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

namespace BadPixxel\BrevoBridge\Tests\Bundle\Processors\Email;

use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Models\AbstractEmailProcessor;
use BadPixxel\BrevoBridge\Tests\Bundle\Interfaces\DummyUrlsAwareInterface;

/**
 * Just a Basic Processor to Complete Tests Emails with Dummy Urls
 */
class DummyUrlsProcessor extends AbstractEmailProcessor
{
    /**
     * @inheritDoc
     */
    public function supports(string $emailClass): bool
    {
        return is_subclass_of($emailClass, DummyUrlsAwareInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function process(AbstractEmail $email): void
    {
        if (!$email instanceof DummyUrlsAwareInterface) {
            return;
        }
        //==============================================================================
        // Setup Email Dummy Urls
        $email->mergeParams(array(
            'urls' => array(
                'home' => "https://www.brevo.com"
            ),
        ));
        //==============================================================================
        // Setup Options Resolver
        $email->getResolver()->setDefault("urls", array());
        $email->getResolver()->setAllowedTypes("urls", "array");
    }
}
