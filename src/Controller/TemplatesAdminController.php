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
use FOS\UserBundle\Model\UserInterface as User;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

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
        $tmplManager = $this->get(TemplateManager::class);
        //==============================================================================
        // Find All Available Emails
        $tmplEmails = array();
        foreach ($tmplManager->getAllEmails() as $emailCode => $emailClass) {
            if (class_exists($emailClass) && $tmplManager->isTemplateAware($emailClass)) {
                $tmplEmails[$emailCode] = $emailClass;
            }
        }

        return $this->renderWithExtraParams("@SendinblueBridge/TemplatesAdmin/list.html.twig", array(
            "tmplEmails" => $tmplEmails,
            "allEmails" => $tmplManager->getAllEmails(),
        ));
    }

    /**
     * Complete Debug of an Email Template
     *
     * @param TemplateManager $tmplManager
     * @param string          $emailCode
     *
     * @return Response
     */
    public function viewAction(TemplateManager $tmplManager, string $emailCode): Response
    {
        //==============================================================================
        // Identify Email Class
        $emailClass = $tmplManager->getEmailByCode($emailCode);
        if (is_null($emailClass)) {
            return $this->redirectToIndex();
        }
        if (!class_exists($emailClass) || !$tmplManager->isTemplateAware($emailClass)) {
            return $this->redirectToIndex();
        }
        //==============================================================================
        // Compile Email Template
        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');
        $tmplHtml = (string) $tmplManager->compile($emailClass);
        $tmplPath = $kernel->getProjectDir().self::TMPL_DIR;
        file_put_contents($tmplPath.self::TMPL_PATH, $tmplHtml);
        //==============================================================================
        // Add Temporary Path to Twig Loader
        /** @var Environment $twig */
        $twig = $this->get('twig');
        /** @var FilesystemLoader $loader */
        $loader = $twig->getLoader();
        $loader->addPath($tmplPath);
        //==============================================================================
        // Find All Available Emails
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
     * @param TemplateManager $tmplManager
     * @param null|string     $emailCode
     *
     * @return Response
     */
    public function updateAction(TemplateManager $tmplManager, $emailCode = null): Response
    {
        /** @var Session $session */
        $session = $this->getRequest()->getSession();
        //==============================================================================
        // Identify Email Class
        $emailClass = $tmplManager->getEmailByCode((string) $emailCode);
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
        if (!$tmplManager->isTemplateAware($emailClass)) {
            $session->getFlashBag()->add('sonata_flash_error', 'Email Class: '.$emailCode.' do not manage Templates');

            return $this->redirectToIndex();
        }
        //==============================================================================
        // Compile Email Template Raw Html
        $rawHtml = $tmplManager->compile($emailClass);
        if (is_null($rawHtml)) {
            $session->getFlashBag()->add('sonata_flash_error', $tmplManager->getLastError());

            return $this->redirectToIndex();
        }
        //==============================================================================
        // Update Email Template On Host
        if (null == $tmplManager->update($emailClass, $rawHtml)) {
            $session->getFlashBag()->add('sonata_flash_error', $tmplManager->getLastError());

            return $this->redirectToIndex();
        }
        $session->getFlashBag()->add('sonata_flash_success', 'Email Template Updated');

        return $this->redirectToIndex();
    }

    /**
     * Update Email Template on SendInBlue
     *
     * @param SmtpManager $smtpManager
     * @param string      $emailCode
     *
     * @return Response
     */
    public function sendAction(SmtpManager $smtpManager, string $emailCode): Response
    {
        /** @var Session $session */
        $session = $this->getRequest()->getSession();
        /** @var User $user */
        $user = $this->getUser();
        //==============================================================================
        // Identify Email Class
        $emailClass = $smtpManager->getEmailByCode($emailCode);
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
        if (!is_subclass_of($emailClass, AbstractEmail::class)) {
            $session->getFlashBag()->add(
                'sonata_flash_error',
                'Email Class: '.$emailClass.' is not an '.AbstractEmail::class
            );

            return $this->redirectToIndex();
        }
        //==============================================================================
        // Send Test Email
        $email = $emailClass::sendDemo($user);
        if (is_null($email)) {
            $session->getFlashBag()->add('sonata_flash_error', $emailClass::getLastError());

            return $this->redirectToIndex();
        }
        $session->getFlashBag()->add('sonata_flash_success', 'Test Email send to '.$user->getEmail());

        return $this->redirectToIndex();
    }

    /**
     * Redirect to List Page
     *
     * @return Response
     */
    private function redirectToIndex(): Response
    {
        return $this->redirectToRoute("admin_badpixxel_sendinblue_templates_list");
    }
}
