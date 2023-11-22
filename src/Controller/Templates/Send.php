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

namespace BadPixxel\BrevoBridge\Controller\Templates;

use BadPixxel\BrevoBridge\Dictionary\TemplatesRoutes;
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use BadPixxel\BrevoBridge\Services\SmtpManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Render a Brevo Email using Local Template Sources
 */
class Send extends CRUDController
{
    public function __construct(
        private SmtpManager $smtpManager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request, string $emailCode): Response
    {
        /** @var Session $session */
        $session = $request->getSession();
        /** @var User $user */
        $user = $this->getUser();
        //==============================================================================
        // Identify Email Class
        $emailClass = $this->smtpManager->getEmailByCode($emailCode);
        if (is_null($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Unable to identify Email');

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        //==============================================================================
        // Verify Email Class
        if (!class_exists($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$emailClass.' was not found');

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        if (!is_subclass_of($emailClass, AbstractEmail::class)) {
            $session->getFlashBag()->add(
                'sonata_flash_error',
                'Email Class: '.$emailClass.' is not an '.AbstractEmail::class
            );

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        //==============================================================================
        // Send Test Email
        $email = $emailClass::sendDemo($user);
        if (is_null($email)) {
            $session->getFlashBag()->add('sonata_flash_error', $emailClass::getLastError());

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        $session->getFlashBag()->add('sonata_flash_success', 'Test Email send to '.$user->getEmail());

        return $this->redirectToRoute(TemplatesRoutes::LIST);
    }
}
