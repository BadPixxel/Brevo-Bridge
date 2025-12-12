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

use BadPixxel\BrevoBridge\Admin\Controller\EmailAdminController;
use BadPixxel\BrevoBridge\Models\AbstractEmailAdmin;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Sonata Admin Class for Users Emails.
 */
#[AutoconfigureTag(
    'sonata.admin',
    attributes: array(
        'model_class' => '%brevo_bridge.emails.class%',
        'controller' => EmailAdminController::class,
        'manager_type' => 'orm',
        'label' => 'Send Emails',
        'group' => 'Brevo',
        'icon' => '<i class="fa far fa-envelope"></i>',
        'pager_type' => 'simple',
    )
)]
class UserEmailsAdmin extends AbstractEmailAdmin
{
}
