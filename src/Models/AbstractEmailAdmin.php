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

namespace BadPixxel\SendinblueBridge\Models;

use Application\DocsBundle\Form\Type\AdminFileType;
use BadPixxel\SendinblueBridge\Entity\AbstractEmailStorage as EmailStorage;
use BadPixxel\SendinblueBridge\Services\SmtpManager;
use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Sonata Admin Class for Emails.
 */
abstract class AbstractEmailAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->remove('edit');
        $collection->remove('create');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('subject')
            ->add('email')
            ->add('md5')
            ->add('sendAt')
            ->add('openAt')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('email')
            ->add('md5')
            ->add('sendAt')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
//        $formMapper->remove('_delete');

        $formMapper
//            ->with('Général', array('class' => 'col-md-6'))
//            ->add('file', AdminFileType::class, array(
//                'required' => !$abstractFile->isValid(),
//                'label' => 'Fichier / Image',
//                'attr' => array('width' => ($isEmebedded ? null : '100%')),
//            ))
//            ->add('email', TextType::class, array())
//            ->remove('_delete')
            ->add('subject')
            ->add('sendAt', DateType::class, array('widget' => 'single_text'))

//            ->add('events', null, array(
//                'template' => '@Advert/Admin/Adverts/advert.stats.html.twig',
//            ))

//            ->add('title', null, array(
//                'required' => false,
//                'sonata_help' => ''
//                    .'<span class="text-info"><i class="fa fa-question-circle"></i>&nbsp;Regénéré si moins de 3 lettres... essayez! </span>',
//                'empty_data' => '',
//            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->updateEmailMetadata();

        $showMapper
            ->with('Contents', array('class' => 'col-md-8'))
            ->add('htmlContent', null, array(
                'template' => '@SendinblueBridge/Admin/html_content.html.twig',
            ))
            ->end()
            ->with('Metadatas', array('class' => 'col-md-4'))
            ->add('email')
            ->add('md5')
            ->add('sendAt')
            ->add('openAt')
            ->add('messageId')
            ->add('uuid')
            ->add('events', null, array(
                'template' => '@SendinblueBridge/Admin/events_list.html.twig',
            ))
            ->end()
        ;
    }

    /**
     * Update Current Email MetaData from Smtp Api.
     *
     * @return void
     */
    private function updateEmailMetadata(): void
    {
        //==============================================================================
        // Load Current Subject
        $storageEmail = $this->getSubject();
        if (!($storageEmail instanceof EmailStorage)) {
            return;
        }
        //==============================================================================
        // Connect to Smtp Manager
        /** @var SmtpManager $smtpManager */
        $smtpManager = $this->getConfigurationPool()->getContainer()->get('badpixxel.sendinblue.smtp');
        //==============================================================================
        // Refresh Email if Needed
        $smtpManager->update($storageEmail, false);
    }
}
