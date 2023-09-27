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

use BadPixxel\Paddock\System\MySql\Controller\GdprAdminActionsTrait;
use Sonata\AdminBundle\Controller\CRUDController;

/**
 * Sonata Admin Sms Controller.
 */
class SmsAdminController extends CRUDController
{
    use GdprAdminActionsTrait;
}
