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

use BadPixxel\BrevoBridge\Services\Sms\SmsManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Render Local Sms Templates List.
 */
class ListView extends CRUDController
{
    public function __construct(
        private readonly SmsManager $manager,
    ) {
    }

    /**
     * Render Local Emails Templates List.
     */
    public function __invoke(): Response
    {
        return $this->renderWithExtraParams("@BrevoBridge/TemplatesAdmin/list.sms.html.twig", array(
            "allSms" => $this->manager->getAll(),
        ));
    }
}
