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

namespace BadPixxel\SendinblueBridge\Admin\Extensions;

use BadPixxel\SendinblueBridge\Interfaces\EmailsAwareInterface;
use FOS\UserBundle\Model\UserInterface as User;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * Add a Tab to User's Sonata Admin Page to show Users Emails logs
 */
class UserEmailsExtension extends AbstractAdminExtension
{
    /**
     * Configure Child Admins (Notary!!).
     *
     * @param AdminInterface      $admin
     * @param MenuItemInterface   $menu
     * @param string              $action
     * @param null|AdminInterface $childAdmin
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function configureSideMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        ?AdminInterface $childAdmin = null
    ): void {
        //==============================================================================
        // Only in EDIT & SHOW Modes
        if (!in_array($action, array('edit', 'show'), true)) {
            return;
        }
        //==============================================================================
        // Get Current Subject
        /** @var null|EmailsAwareInterface|User $subject */
        $subject = $admin->getSubject();
        if (!($subject instanceof User) || !($subject instanceof EmailsAwareInterface)) {
            return;
        }
        //==============================================================================
        // Subject Has Emails
        $first = $subject->getEmails()->first();
        if ($first) {
            //==============================================================================
            // Detect Storage Class Admin
            $emailAdmin = $admin->getConfigurationPool()->getAdminByClass(get_class($first));
            if ($emailAdmin) {
                $menu->addChild($subject->getEmails()->count().' Emails', array(
                    'uri' => $emailAdmin->generateObjectUrl('list', $first, array('email' => $subject->getEmail())),
                ))
                    ->setAttribute('icon', 'fa fa-envelope text-primary')
                ;
            }
        }
    }
}
