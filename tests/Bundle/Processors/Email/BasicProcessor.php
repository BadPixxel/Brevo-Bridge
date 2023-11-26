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
use BadPixxel\BrevoBridge\Tests\Bundle\Emails\Basic;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Just a Basic Processor to Complete Basic Tests Emails
 */
class BasicProcessor extends AbstractEmailProcessor
{
    public function __construct(
        private RequestStack $requestStack
    ) {
    }

    /**
     * @inheritDoc
     */
    public function supports(string $emailClass): bool
    {
        return Basic::class == $emailClass;
    }

    /**
     * @inheritDoc
     */
    public function process(AbstractEmail $email): void
    {
        if (!$email instanceof Basic) {
            return;
        }
        //==============================================================================
        // Setup template as it was not configured on Email Creation
        $email->getEmail()->setTemplateId(1);
        //==============================================================================
        // Load Current Parameters
        /** @var \stdClass $params */
        $params = $email->getEmail()->getParams();
        //==============================================================================
        // Complete Text
        $params->text .= " <br /> <br /> Cool, I've been Updated by Processor !!";
        $params->text .= " <br /> <br /> Source website was ";
        $params->text .= $this->requestStack->getMainRequest()?->getSchemeAndHttpHost();
        $email->getEmail()->setParams($params);

        $email->getResolver()->setDefault("urls", array());
    }
}
