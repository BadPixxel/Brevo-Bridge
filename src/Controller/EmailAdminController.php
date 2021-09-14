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

use BadPixxel\SendinblueBridge\Entity\AbstractEmailStorage as Email;
use BadPixxel\SendinblueBridge\Services\SmtpManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
     * @param SmtpManager $smtpManager
     * @param null|string $id
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function refreshAction(SmtpManager $smtpManager, $id = null): Response
    {
        //====================================================================//
        // Load Email Object
        /** @var Email $email */
        $email = $this->admin->getObject($id);
        if (null == $email) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }
        //==============================================================================
        // Refresh Email (Forced)
        $smtpManager->update($email, true);
        $this->addFlash('sonata_flash_success', 'Email Status Refreshed');
        //==============================================================================
        // Load Referer Url
        /** @var string $referer */
        $referer = $this->getRequest()->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
        //====================================================================//
        // Redirect to View Page
        return $this->redirect(
            $this->admin->generateObjectUrl("show", $email)
        );
    }

    /**
     * Refresh Email Events.
     *
     * @param ProxyQueryInterface $selectedModelQuery
     *
     * @return RedirectResponse
     */
    public function batchActionRefresh(ProxyQueryInterface $selectedModelQuery): RedirectResponse
    {
        //==============================================================================
        // Security Check
        if (!$this->admin->isGranted('SHOW')) {
            throw new AccessDeniedException();
        }
        //==============================================================================
        // Load Selected Models
        $selectedModels = $selectedModelQuery->execute();
        //==============================================================================
        // Refresh Email (Forced)
        try {
            foreach ($selectedModels as $selectedModel) {
                if (!($selectedModel instanceof Email)) {
                    throw new Exception();
                }
                SmtpManager::getInstance()->update($selectedModel, true);
            }
        } catch (Exception $exception) {
            $this->addFlash(
                'sonata_flash_error',
                sprintf("Email refresh failed: %s", $exception->getMessage())
            );

            return new RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }
        $this->addFlash(
            'sonata_flash_success',
            sprintf("%d Emails refreshed", count((array) $selectedModels))
        );

        return new RedirectResponse(
            $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }
}
