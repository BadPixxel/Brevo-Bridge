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

use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use BadPixxel\BrevoBridge\Services\Emails\EmailsStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Execute WebHook Actions for Brevo Emails Updates
 */
class WebHooksController extends AbstractController
{
    public function __invoke(
        Request       $request,
        EmailsManager $manager,
        EmailsStorage $storage,
    ): Response {
        //==============================================================================
        // Safety Check
        $messageId = $this->verify($request);
        //====================================================================//
        // Search for this Email
        $storageEmail = $storage->findByMessageId($messageId);
        if (!$storageEmail) {
            return new Response("No Storage found for this Email");
        }
        //====================================================================//
        // Refresh Email Events
        $manager->update($storageEmail, true);

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
            /** @var array $contents */
            $contents = json_decode((string) $request->getContent(), true);
            $messageId = $contents["message-id"] ?? null;
        }
        if (empty($messageId) || !is_scalar($messageId)) {
            throw new BadRequestHttpException('No Message Id Found');
        }

        //====================================================================//
        // Return Message Id
        return (string) $messageId;
    }
}
