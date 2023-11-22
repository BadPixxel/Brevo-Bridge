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

namespace BadPixxel\BrevoBridge\Controller;

use BadPixxel\BrevoBridge\Dictionary\TemplatesRoutes;
use BadPixxel\BrevoBridge\Services\TemplateManager;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Admin Controller for SendInBlue Bridge Emails Templates Management
 */
class TemplatesAdminController extends Controller
{
    /**
     * @var TemplateManager
     */
    private TemplateManager $tmplManager;

    /**
     * Constructor
     */
    public function __construct(TemplateManager $tmplManager)
    {
        $this->tmplManager = $tmplManager;
    }

    /**
     * Render User Dashboard.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function listAction(Request $request): Response
    {
        //==============================================================================
        // Find All Available Emails
        $tmplEmails = array();
        foreach ($this->tmplManager->getAllEmails() as $emailCode => $emailClass) {
            if (class_exists($emailClass) && $this->tmplManager->isTemplateAware($emailClass)) {
                $tmplEmails[$emailCode] = $emailClass;
            }
        }

        return $this->renderWithExtraParams("@BrevoBridge/TemplatesAdmin/list.html.twig", array(
            "tmplEmails" => $tmplEmails,
            "allEmails" => $this->tmplManager->getAllEmails(),
        ));
    }

    /**
     * Update Email Template on SendInBlue
     *
     * @param Request     $request
     * @param null|string $emailCode
     *
     * @return Response
     */
    public function updateAction(Request $request, string $emailCode = null): Response
    {
        /** @var Session $session */
        $session = $request->getSession();
        //==============================================================================
        // Identify Email Class
        $emailClass = $this->tmplManager->getEmailByCode((string) $emailCode);
        if (is_null($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Unable to identify Email');

            return $this->redirectToIndex();
        }
        //==============================================================================
        // Verify Email Class
        if (!class_exists($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$emailClass.' was not found');

            return $this->redirectToIndex();
        }
        //==============================================================================
        // Check if Email Needs to Be Compiled
        if (!$this->tmplManager->isTemplateAware($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$emailCode.' do not manage Templates');

            return $this->redirectToIndex();
        }
        //==============================================================================
        // Compile Email Template Raw Html
        $rawHtml = $this->tmplManager->compile($emailClass);
        if (is_null($rawHtml)) {
            $session->getFlashBag()->add('sonata_flash_error', $this->tmplManager->getLastError());

            return $this->redirectToIndex();
        }
        //==============================================================================
        // Update Email Template On Host
        if (null == $this->tmplManager->update($emailClass, $rawHtml)) {
            $session->getFlashBag()->add('sonata_flash_error', $this->tmplManager->getLastError());

            return $this->redirectToIndex();
        }
        $session->getFlashBag()->add('sonata_flash_success', 'Email Template Updated');

        return $this->redirectToIndex();
    }

    /**
     * Redirect to List Page
     *
     * @return Response
     */
    private function redirectToIndex(): Response
    {
        return $this->redirectToRoute(TemplatesRoutes::LIST);
    }
}
