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

use BadPixxel\BrevoBridge\Entity\AbstractEmailStorage;
use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use BadPixxel\Paddock\System\MySql\Controller\GdprAdminActionsTrait;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Sonata Admin Emails Controller.
 */
class EmailAdminController extends CRUDController
{
    use GdprAdminActionsTrait;

    /**
     * Preview Email Contents.
     *
     * @param null|int $id
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function previewAction(int $id = null): Response
    {
        //====================================================================//
        // Load Email Object
        $email = $this->admin->getObject($id);
        if (!$email instanceof AbstractEmailStorage) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        //====================================================================//
        // Output the raw Email Contents
        return new Response($email->getHtmlContent());
    }

    /**
     * Refresh Email Events.
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function refreshAction(Request $request, EmailsManager $manager, int $id = null): Response
    {
        //====================================================================//
        // Load Email Object
        $email = $this->admin->getObject($id);
        if (!$email instanceof AbstractEmailStorage) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }
        //==============================================================================
        // Refresh Email (Forced)
        $manager->update($email, true);
        $this->addFlash('sonata_flash_success', 'Email Status Refreshed');
        //==============================================================================
        // Load Referer Url
        /** @var string $referer */
        $referer = $request->headers->get('referer');
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
     */
    public function batchActionRefresh(
        ProxyQueryInterface $selectedModelQuery,
        EmailsManager $manager
    ): RedirectResponse {
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
                if (!($selectedModel instanceof AbstractEmailStorage)) {
                    throw new Exception();
                }
                $manager->update($selectedModel, true);
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
