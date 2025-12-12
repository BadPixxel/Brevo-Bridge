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

namespace BadPixxel\BrevoBridge\Admin\Controller\Emails;

use BadPixxel\BrevoBridge\Dictionary\TemplatesRoutes;
use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Render a Brevo Email using Local Template Sources
 */
class Send extends CRUDController
{
    public function __construct(
        private readonly EmailsManager   $manager,
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
            $this->addFlash('sonata_flash_error', 'Unable to identify Email');

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        //==============================================================================
        // Send Test Email
        $sendEmail = $email::sendDemo($user);
        if (is_null($sendEmail)) {
            $this->addFlash('sonata_flash_error', $email::getLastError());

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        $this->addFlash('sonata_flash_success', 'Test Email send to '.$user->getEmail());

        return $this->redirectToRoute(TemplatesRoutes::LIST);
    }
}
