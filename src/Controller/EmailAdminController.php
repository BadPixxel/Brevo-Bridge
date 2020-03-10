<?php

/*
 *  Copyright (C) 2020 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Controller;

use BadPixxel\SendinblueBridge\Entity\AbstractEmailStorage as Email;
use BadPixxel\SendinblueBridge\Services\SmtpManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sonata Admin Emails Controller.
 */
class EmailAdminController extends CRUDController
{
    /**
     * Preview Email Contents.
     *
     * @param null|string $id
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function previewAction($id = null): Response
    {
        //====================================================================//
        // Load Email Object
        /** @var Email $email */
        $email = $this->admin->getObject($id);
        if (null == $email) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }
        //====================================================================//
        // Output the raw Email Contents
        return new Response($email->getHtmlContent());
    }

    /**
     * Refresh Email Events.
     *
     * @param null|string $id
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function refreshAction($id = null): Response
    {
        //====================================================================//
        // Load Email Object
        /** @var Email $email */
        $email = $this->admin->getObject($id);
        if (null == $email) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }
        //==============================================================================
        // Connect to Smtp Manager
        /** @var SmtpManager $smtpManager */
        $smtpManager = $this->get('badpixxel.sendinblue.smtp');
        //==============================================================================
        // Refresh Email (Forced)
        $smtpManager->update($email, true);
        //==============================================================================
        // Load Referer Url
        /** @var string $referer */
        $referer = $this->getRequest()->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        //====================================================================//
        // Redirect to View Page
        return $this->redirectToRoute(
            'admin_application_sendinbluebridge_email_show',
            array('id' => $email->getId())
        );
    }
}
