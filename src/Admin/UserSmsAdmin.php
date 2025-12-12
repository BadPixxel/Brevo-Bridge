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

namespace BadPixxel\BrevoBridge\Admin;

use BadPixxel\BrevoBridge\Admin\Controller\SmsAdminController;
use BadPixxel\BrevoBridge\Models\AbstractSmsAdmin;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Sonata Admin Class for Users Sms.
 */
#[AutoconfigureTag(
    'sonata.admin',
    attributes: array(
        'model_class' => '%brevo_bridge.sms.class%',
        'controller' => SmsAdminController::class,
        'manager_type' => 'orm',
        'label' => 'Send Sms',
        'group' => 'Brevo',
        'icon' => '<i class="fa far fa-envelope"></i>',
        'pager_type' => 'simple',
    )
)]
class UserSmsAdmin extends AbstractSmsAdmin
{
}
