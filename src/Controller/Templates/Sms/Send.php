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
use BadPixxel\BrevoBridge\Services\Sms\SmsManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\UserBundle\Model\UserInterface as User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Send a Brevo Fake Sms
 */
class Send extends CRUDController
{
    public function __construct(
        private readonly SmsManager  $manager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(string $smsId): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        //==============================================================================
        // Identify Sms Class
        $sms = $this->manager->getSmsById($smsId);
        if (!$sms) {
            $this->addFlash('sonata_flash_error', 'Unable to identify Sms');

            return $this->redirectToRoute(TemplatesRoutes::SMS_LIST);
        }
        //==============================================================================
        // Send Test Sms
        $sendSms = $sms::sendDemo($user);
        if (is_null($sendSms)) {
            $this->addFlash('sonata_flash_error', $sms::getLastError());

            return $this->redirectToRoute(TemplatesRoutes::SMS_LIST);
        }
        $this->addFlash('sonata_flash_success', 'Test Sms was send');

        return $this->redirectToRoute(TemplatesRoutes::SMS_LIST);
    }
}
