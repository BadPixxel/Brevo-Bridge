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
 * Sonata Admin Class for Sms.
 */
abstract class AbstractSmsAdmin extends Admin
{
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
     * @param RouteCollectionInterface $collection
     *
     * @return void
     */
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('edit');
        $collection->remove('create');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('sendAt')
            ->add('email')
            ->add('subject')
            ->add('textContent')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('email')
            ->add('subject')
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
            ->add('textContent', null)
            ->end()
            ->with('Metadatas', array('class' => 'col-md-4'))
            ->add('email')
            ->add('subject')
            ->add('md5')
            ->add('sendAt')
            ->add('messageId')
            ->end()
        ;
    }
}
