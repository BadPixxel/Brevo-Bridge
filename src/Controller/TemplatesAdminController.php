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

use BadPixxel\SendinblueBridge\Models\AbstractEmail;
use BadPixxel\SendinblueBridge\Services\SmtpManager;
use BadPixxel\SendinblueBridge\Services\TemplateManager;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Admin Controller for SendInBlue Bridge Emails Templates Management
 */
class TemplatesAdminController extends Controller
{
    /**
     * @var string
     */
    const TMPL_DIR = "/var/";

    /**
     * @var string
     */
    const TMPL_PATH = "/var/sib_email_template.html.twig";

    /**
     * Render User Dashboard.
     *
     * @return Response
     */
    public function listAction()
    {
        /** @var TemplateManager $tmplManager */
        $tmplManager = $this->get('badpixxel.sendinblue.templates');
        //==============================================================================
        // Find All Avalaible Emails
        $tmplEmails = array();
        foreach ($tmplManager->getAllEmails() as $emailCode => $emailClass) {
            if (class_exists($emailClass) && $tmplManager->isTemplateAware($emailClass)) {
                $tmplEmails[$emailCode] = $emailClass;
            }
        }

        return $this->render("@SendinblueBridge/TemplatesAdmin/list.html.twig", array(
            "tmplEmails" => $tmplEmails,
            "allEmails" => $tmplManager->getAllEmails(),
        ));
    }

    /**
     * Complete Debug of an Email Template
     *
     * @param null|string $id
     *
     * @return Response
     */
    public function showAction($id = null)
    {
        /** @var TemplateManager $tmplManager */
        $tmplManager = $this->get('badpixxel.sendinblue.templates');
        //==============================================================================
        // Identify Email Class
        $emailClass = $tmplManager->getEmailByCode($id);
        if (is_null($emailClass)) {
            return $this->redirectToRoute('badpixxel_sendinblue_tmpl_debug_index');
        }
        if (!class_exists($emailClass) || !$tmplManager->isTemplateAware($emailClass)) {
            return $this->redirectToRoute('badpixxel_sendinblue_tmpl_debug_index');
        }
        //==============================================================================
        // Compile Email Template
        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');
        $tmplHtml = (string) $tmplManager->compile($emailClass);
        $tmplPath = $kernel->getProjectDir().self::TMPL_PATH;
        file_put_contents($tmplPath, $tmplHtml);
        //==============================================================================
        // Find All Avalaible Emails
        $tmplEmails = array();
        foreach ($tmplManager->getAllEmails() as $code => $class) {
            if (class_exists($class) && $tmplManager->isTemplateAware($class)) {
                $tmplEmails[$code] = $class;
            }
        }
        /** @var UserInterface $user */
        $user = $this->getUser();

        return $this->render("@SendinblueBridge/Debug/email_view.html.twig", array(
            "tmplPath" => $tmplPath,
            'tmplParams' => $tmplManager->getTmplParameters($emailClass, $user),
            "tmplEmails" => $tmplEmails,
            "allEmails" => $tmplManager->getAllEmails(),
        ));
    }

    /**
     * Update Email Template on SendInBlue
     *
     * @param null|string $id
     *
     * @return Response
     */
    public function exportAction($id = null)
    {
        /** @var TemplateManager $tmplManager */
        $tmplManager = $this->get('badpixxel.sendinblue.templates');
        /** @var Session $session */
        $session = $this->getRequest()->getSession();
        //==============================================================================
        // Identify Email Class
        $emailClass = $tmplManager->getEmailByCode($id);
        if (is_null($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Unable to identify Email');

            return $this->redirectToList();
        }
        //==============================================================================
        // Verify Email Class
        if (!class_exists($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$emailClass.' was not found');

            return $this->redirectToList();
        }
        //==============================================================================
        // Check if Email Needs to Be Compiled
        if (!$tmplManager->isTemplateAware($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$id.' do not manage Templates');

            return $this->redirectToList();
        }
        //==============================================================================
        // Compile Email Template Raw Html
        $rawHtml = $tmplManager->compile($emailClass);
        if (is_null($rawHtml)) {
            $session->getFlashBag()->add('sonata_flash_error', $tmplManager->getLastError());

            return $this->redirectToList();
        }
        //==============================================================================
        // Update Email Template On Host
        if (null == $tmplManager->update($emailClass, $rawHtml)) {
            $session->getFlashBag()->add('sonata_flash_error', $tmplManager->getLastError());

            return $this->redirectToList();
        }
        $session->getFlashBag()->add('sonata_flash_success', 'Email Template Updated');

        return $this->redirectToList();
    }

    /**
     * Update Email Template on SendInBlue
     *
     * @param null|string $id
     *
     * @return Response
     */
    public function sendAction($id = null)
    {
        /** @var SmtpManager $smtpManager */
        $smtpManager = $this->get('badpixxel.sendinblue.smtp');
        /** @var Session $session */
        $session = $this->getRequest()->getSession();
        //==============================================================================
        // Identify Email Class
        $emailClass = $smtpManager->getEmailByCode($id);
        if (is_null($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Unable to identify Email');

            return $this->redirectToList();
        }
        //==============================================================================
        // Verify Email Class
        if (!class_exists($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$emailClass.' was not found');

            return $this->redirectToList();
        }
        if (!is_subclass_of($emailClass, AbstractEmail::class)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$emailClass.' is not an '.AbstractEmail::class);

            return $this->redirectToList();
        }
        //==============================================================================
        // Send Test Email
        $email = $emailClass::sendDemo($this->getUser());
        if (is_null($email)) {
            $session->getFlashBag()->add('sonata_flash_error', $emailClass::getLastError());

            return $this->redirectToList();
        }
        $session->getFlashBag()->add('sonata_flash_success', 'Test Email send to '.$this->getUser()->getEmail());

        return $this->redirectToList();
    }
}
