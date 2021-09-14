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

namespace BadPixxel\SendinblueBridge\Models;

use BadPixxel\SendinblueBridge\Entity\AbstractEmailStorage as EmailStorage;
use BadPixxel\SendinblueBridge\Services\SmtpManager;
use Exception;
use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Sonata Admin Class for Emails.
 */
abstract class AbstractEmailAdmin extends Admin
{
    /**
     * @param string $action
     * @param mixed  $object
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function configureActionButtons($action, $object = null): array
    {
        $list = parent::configureActionButtons($action, $object);

        $list['refresh']['template'] = '@SendinblueBridge/Admin/action_refresh.html.twig';

        return $list;
    }

    /**
     * Dynamically Adjust default filters.
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function getFilterParameters()
    {
        $extraFilters = array();
        //==============================================================================
        // Filter List on User Email
        if ($this->hasRequest() && !empty($this->getRequest()->get('email'))) {
            $extraFilters['email'] = array(
                'value' => $this->getRequest()->get('email'),
            );
        }

        return array_replace_recursive(parent::getFilterParameters(), $extraFilters);
    }

    /**
     * Configure batch Actions
     *
     * @return array
     */
    public function getBatchActions(): array
    {
        $actions = array();
        if ($this->hasRoute('show') && $this->isGranted('SHOW')) {
            $actions['refresh'] = array(
                'label' => "Refresh",
                'ask_confirmation' => false
            );
        }

        return array_replace_recursive($actions, parent::getBatchActions());
    }
    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection): void
    {
        $collection->remove('edit');
        $collection->remove('create');
        // Email Preview
        $collection->add('preview', $this->getRouterIdParameter().'/preview');
        // Refresh Email Events
        $collection->add('refresh', $this->getRouterIdParameter().'/refresh');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
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
                    'refresh' => array(
                        'template' => '@SendinblueBridge/Admin/list__action_email_refresh.html.twig',
                    )
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
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
    protected function configureShowFields(ShowMapper $showMapper): void
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
        // Connect to Container
        $container = $this->getConfigurationPool()->getContainer();
        if (!$container) {
            return;
        }
        //==============================================================================
        // Connect to Smtp Manager
        /** @var SmtpManager $smtpManager */
        $smtpManager = $container->get(SmtpManager::class);
        //==============================================================================
        // Refresh Email if Needed
        $smtpManager->update($storageEmail, false);
    }
}
