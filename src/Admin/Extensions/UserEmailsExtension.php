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

use BadPixxel\SendinblueBridge\Form\Type\EmailViewType;
use BadPixxel\SendinblueBridge\Interfaces\EmailsAwareInterface;
use BadPixxel\SendinblueBridge\Services\SmtpManager;
use Exception;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * Add a Tab to User's Sonata Admin Page to show Users Emails logs
 */
class UserEmailsExtension extends AbstractAdminExtension
{
    /**
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper): void
    {
        if (!$this->updateEmailMetadata($formMapper)) {
            return;
        }

        $formMapper
            ->tab('Emails')
            ->with('Send Emails', array('class' => 'col-md-12'))
            ->add(
                'emails',
                CollectionType::class,
                array(
                    'label' => false,
                    'entry_type' => EmailViewType::class,
                ),
                array(
                )
            )
            ->end()
            ->end()
        ;
    }

    /**
     * Update Current User Emails MetaData from Smtp Api.
     *
     * @return bool
     */
    private function updateEmailMetadata(FormMapper $formMapper): bool
    {
        //==============================================================================
        // Get Parent Admin Class
        $admin = $formMapper->getAdmin();
        if (!($admin instanceof AbstractAdmin)) {
            throw new Exception('Admin Class is Wrong');
        }
        //==============================================================================
        // Load Current Subject
        $subject = $admin->getSubject();
        if (!($subject instanceof EmailsAwareInterface) || !$subject->hasEmails()) {
            return false;
        }
        //==============================================================================
        // Connect to Container
        $container = $admin->getConfigurationPool()->getContainer();
        if (!$container) {
            return false;
        }
        //==============================================================================
        // Connect to Smtp Manager
        /** @var SmtpManager $smtpManager */
        $smtpManager = $container->get(SmtpManager::class);
        //==============================================================================
        // Refresh Email if Needed
        foreach ($subject->getEmails() as $storageEmail) {
            $smtpManager->update($storageEmail, false);
        }

        return true;
    }
}
