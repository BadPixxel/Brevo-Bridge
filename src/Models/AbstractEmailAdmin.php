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

namespace BadPixxel\SendinblueBridge\Models;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

/**
 * Sonata Admin Class for Emails.
 */
abstract class AbstractEmailAdmin extends Admin
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $list = parent::configureActionButtons($buttonList, $action, $object);

        $list['refresh']['template'] = '@SendinblueBridge/Admin/action_refresh.html.twig';

        return $list;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDefaultFilterValues(array &$filterValues): void
    {
        //==============================================================================
        // Filter List on User Email
        if ($this->hasRequest() && !empty($this->getRequest()->get('email'))) {
            $filterValues['email'] = array(
                'value' => $this->getRequest()->get('email'),
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureBatchActions(array $actions): array
    {
        if ($this->hasRoute('show') && $this->isGranted('SHOW')) {
            $actions['refresh'] = array(
                'label' => "Refresh",
                'ask_confirmation' => false
            );
        }

        return $actions;
    }

    /**
     * @param RouteCollectionInterface $collection
     *
     * @return void
     */

    protected function configureRoutes(RouteCollectionInterface $collection): void
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
    protected function configureListFields(ListMapper $list): void
    {
        $list
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
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email')
            ->add('md5')
            ->add('sendAt')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        // display the first page (default = 1)
        $sortValues[DatagridInterface::PAGE] = 1;

        // reverse order (default = 'ASC')
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';

        // name of the ordered field (default = the model's id field, if any)
        $sortValues[DatagridInterface::SORT_BY] = 'sendAt';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
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
}
