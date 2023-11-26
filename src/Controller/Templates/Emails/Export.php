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

namespace BadPixxel\BrevoBridge\Controller\Templates\Emails;

use BadPixxel\BrevoBridge\Dictionary\TemplatesRoutes;
use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use BadPixxel\BrevoBridge\Services\TemplateManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Export Email Template Html to Brevo
 */
class Export extends CRUDController
{
    public function __construct(
        private readonly EmailsManager   $manager,
        private readonly TemplateManager        $templates,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(string $emailId): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        //==============================================================================
        // Identify Email Class
        $email = $this->manager->getEmailById($emailId);
        if (!$email) {
            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        //==============================================================================
        // Generate a Fake Email
        $fakeEmail = $this->manager->fake($email, $user);
        if (!$fakeEmail) {
            $this->addFlash('sonata_flash_error', $this->manager->getLastError());

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        //==============================================================================
        // Update Email Template On Host
        if (null == $this->templates->update($email)) {
            $this->addFlash('sonata_flash_error', $this->templates->getLastError());

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        $this->addFlash('sonata_flash_success', 'Email Template Updated');

        return $this->redirectToRoute(TemplatesRoutes::LIST);
    }
}
