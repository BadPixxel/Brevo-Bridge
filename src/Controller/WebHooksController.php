<?php

/*
 *  Copyright (C) 2021 BadPixxel <www.badpixxel.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace BadPixxel\SendinblueBridge\Controller;

use BadPixxel\SendinblueBridge\Services\SmtpManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * SendInBlue WebHooks Controller
 */
class WebHooksController extends AbstractController
{
    //====================================================================//
    //  WEBHOOKS MANAGEMENT
    //====================================================================//

    /**
     * Execute WebHook Actions for SendInblue Emails Updates
     *
     * @param Request     $request
     * @param SmtpManager $smtpManager
     *
     * @return Response
     */
    public function indexAction(Request $request, SmtpManager $smtpManager): Response
    {
        //==============================================================================
        // Safety Check
        $messageId = $this->verify($request);
        //====================================================================//
        // Search for this Email
        $storageEmail = $smtpManager->findByMessageId($messageId);
        if (!$storageEmail) {
            return new Response("No Storage found for this Email");
        }
        //====================================================================//
        // Refresh Email Events
        $smtpManager->update($storageEmail, true);

        return new Response("Ok");
    }

    /**
     * Verify Request is Conform
     *
     * @param Request $request
     *
     * @return string
     *
     * @throw BadRequestHttpException
     */
    private function verify(Request $request) : string
    {
        //====================================================================//
        // Verify Request is POST
        if (!$request->isMethod('POST')) {
            throw new BadRequestHttpException('Wrong Request Method');
        }
        //====================================================================//
        // Verify Message Id Data is Available in POST
        $messageId = $request->request->get("message-id");
        if (empty($messageId)) {
            //====================================================================//
            // Fallback to Raw POST Contents
            $messageId = json_decode((string) $request->getContent(), true)["message-id"];
        }
        if (empty($messageId) || !is_scalar($messageId)) {
            throw new BadRequestHttpException('No Message Id Found');
        }
        //====================================================================//
        // Return Message Id
        return (string) $messageId;
    }
}
