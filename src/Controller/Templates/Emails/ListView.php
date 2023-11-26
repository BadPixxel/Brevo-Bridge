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

namespace BadPixxel\BrevoBridge\Controller\Templates\Emails;

use BadPixxel\BrevoBridge\Services\Emails\EmailsManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Render Local Emails Templates List.
 */
class ListView extends CRUDController
{
    public function __construct(
        private readonly EmailsManager $manager,
    ) {
    }

    /**
     * Render Local Emails Templates List.
     */
    public function __invoke(): Response
    {
        //==============================================================================
        // Find All Available Emails
        $allEmails = $tmplEmails = array();
        foreach ($this->manager->getAll() as $emailId => $email) {
            $emailClass = get_class($email);
            $allEmails[$emailId] = $emailClass;
            if ($this->manager->isTemplateProvider($emailClass)) {
                $tmplEmails[$emailId] = $emailClass;
            }
        }

        return $this->renderWithExtraParams("@BrevoBridge/TemplatesAdmin/list.html.twig", array(
            "tmplEmails" => $tmplEmails,
            "allEmails" => $allEmails,
        ));
    }
}
