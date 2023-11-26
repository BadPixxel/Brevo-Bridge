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

namespace BadPixxel\BrevoBridge\Controller\Templates\Sms;

use BadPixxel\BrevoBridge\Dictionary\TemplatesRoutes;
use BadPixxel\BrevoBridge\Interfaces\HtmlTemplateAwareInterface;
use BadPixxel\BrevoBridge\Services\Sms\SmsManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Render a Demo Sms
 */
class Preview extends CRUDController
{
    public function __construct(
        private readonly SmsManager   $manager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Request $request, string $smsId): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        //==============================================================================
        // Identify Sms Class
        $sms = $this->manager->getSmsById($smsId);
        if (!$sms) {
            return $this->redirectToRoute(TemplatesRoutes::SMS_LIST);
        }
        //==============================================================================
        // Generate a Fake Sms
        $fakeSms = $this->manager->fake($sms, $user);
        if (!$fakeSms) {
            $this->addFlash('sonata_flash_error', $this->manager->getLastError());

            return $this->redirectToRoute(TemplatesRoutes::SMS_LIST);
        }

        return $this->renderWithExtraParams("@BrevoBridge/Debug/sms_view.html.twig", array(
            "smsContents" => $fakeSms->getContents(),
        ));
    }
}
