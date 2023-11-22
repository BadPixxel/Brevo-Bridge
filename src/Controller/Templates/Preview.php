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
use BadPixxel\BrevoBridge\Interfaces\HtmlTemplateAwareInterface;
use BadPixxel\BrevoBridge\Services\RawHtmlRenderer;
use BadPixxel\BrevoBridge\Services\TemplateManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Render a Demo Email using Brevo Account Template by ID
 */
class Preview extends CRUDController
{
    public function __construct(
        private TemplateManager $tmplManager,
        private RawHtmlRenderer $rawHtmlRenderer
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
        $emailClass = $this->tmplManager->getEmailByCode($emailCode);
        if (is_null($emailClass) || !class_exists($emailClass)) {
            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        if (!is_subclass_of($emailClass, HtmlTemplateAwareInterface::class)) {
            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }
        //==============================================================================
        // Fetch Email Template from API
        $smtpTemplate = $this->tmplManager->get($emailClass, $user);
        if (!$smtpTemplate) {
            $session->getFlashBag()->add('sonata_flash_error', $this->tmplManager->getLastError());

            return $this->redirectToRoute(TemplatesRoutes::LIST);
        }

        //==============================================================================
        // Render Raw Html Template
        return $this->rawHtmlRenderer->render(
            $smtpTemplate->getHtmlContent(),
            $this->tmplManager->getTmplParameters($emailClass, $user)
        );
    }
}
