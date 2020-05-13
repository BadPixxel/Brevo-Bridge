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

use BadPixxel\SendinblueBridge\Services\TemplateManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Emails Templates Debugging Actions.
 */
class DebugController extends Controller
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
     * Index Page for Emails Templates Debug
     *
     * @Route("/", name="badpixxel_sendinblue_tmpl_debug_index")
     *
     * @return Response
     */
    public function indexAction(): Response
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

        return $this->render("@SendinblueBridge/Debug/emails_list.html.twig", array(
            "tmplEmails" => $tmplEmails,
            "allEmails" => $tmplManager->getAllEmails(),
        ));
    }

    /**
     * Complete Debug of an Email Template
     *
     * @Route("/{emailCode}/view", name="badpixxel_sendinblue_tmpl_debug_view")
     *
     * @return Response
     */
    public function viewAction(string $emailCode): Response
    {
        /** @var TemplateManager $tmplManager */
        $tmplManager = $this->get('badpixxel.sendinblue.templates');
        //==============================================================================
        // Identify Email Class
        $emailClass = $tmplManager->getEmailByCode($emailCode);
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
     * Debug of a Partial Email Mjml Template Block
     *
     * @Route("/{emailCode}/mjml/{tmplCode}", name="badpixxel_sendinblue_tmpl_debug_mjml")
     *
     * @param string $emailCode Email Code for Parameters Generation
     * @param string $tmplCode  Twig Source Mjml Block Template
     * @param string|null $tmplStyles  Twig Source Mjml Styles
     *
     * @return Response
     */
    public function mjmlAction(string $emailCode, string $tmplCode, string $tmplStyles = null): Response
    {
        /** @var TemplateManager $tmplManager */
        $tmplManager = $this->get('badpixxel.sendinblue.templates');
        //==============================================================================
        // Identify Email Class
        $emailClass = $tmplManager->getEmailByCode($emailCode);
        if (is_null($emailClass)) {
            return new Response("Error: Email Class not Found");
        }
        if (!class_exists($emailClass) || !$tmplManager->isTemplateAware($emailClass)) {
            return new Response("Error: Email Class not Found");
        }
        //==============================================================================
        // Load Mjml Convert
        $mjmlConverter = $tmplManager->getMjmlConverter();
        if (!$mjmlConverter) {
            return new Response("Error: Mjml Converter not Available");
        }
        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');
        //==============================================================================
        // Compile Mjml Block Template to Html
        /** @var EngineInterface $twig */
        $twig = $this->get('templating');
        $tmplMjml = (string) $twig->render("@SendinblueBridge/Debug/mjml_block.html.twig", array(
            "tmplStyles" => $tmplStyles,
            "tmplPath" => $tmplCode,
            "tmplParams" => array(),
        ));
        $tmplHtml = $mjmlConverter->toHtml($tmplMjml);
        $tmplPath = $kernel->getProjectDir().self::TMPL_DIR;
        $tmplPath .= 'sib_'.md5($tmplCode).'.html.twig';
        //==============================================================================
        // Store Template Html to Disk
        file_put_contents($tmplPath, $tmplHtml);
        /** @var UserInterface $user */
        $user = $this->getUser();

        return $this->render($tmplPath, $tmplManager->getTmplParameters($emailClass, $user));
    }
}