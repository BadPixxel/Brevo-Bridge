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

use BadPixxel\BrevoBridge\Services\TemplateManager;
use Exception;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Simulate an Email &n Render Raw Html Contents
 */
class EmailSimulator
{
    public function __construct(
        private readonly EmailsManager   $manager,
        private readonly TemplateManager $templates,
        private readonly RawHtmlRenderer $renderer
    ) {
    }

    /**
     * @param class-string        $emailClass
     * @param array|UserInterface $toUsers
     * @param mixed               ...$args
     *
     * @throws Exception
     */
    public function simulate(string $emailClass, array|UserInterface $toUsers, mixed ...$args): ?string
    {
        //==============================================================================
        // Identify Email Class
        $email = $this->manager->getEmail($emailClass);
        if (!$email) {
            return null;
        }
        //==============================================================================
        // Generate a Fake Email
        $buildEmail = $this->manager->build($email, $toUsers, $args);
        if (!$buildEmail) {
            return null;
        }
        //==============================================================================
        // Fetch Email Template from API
        $smtpTemplate = $this->templates->get($buildEmail);
        if (!$smtpTemplate) {
            return null;
        }

        //==============================================================================
        // Render Raw Html Template
        return $this->renderer->renderRaw(
            $smtpTemplate->getHtmlContent(),
            $this->templates->getTmplParameters($buildEmail)
        );
    }
}
