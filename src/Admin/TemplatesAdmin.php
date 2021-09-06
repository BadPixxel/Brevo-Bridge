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

namespace BadPixxel\SendinblueBridge\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Admin Class for SendInBlue Bridge Emails Templates Management
 */
class TemplatesAdmin extends Admin
{
    /**
     * The base route name used to generate the routing information.
     *
     * @var string
     */
    protected $baseRouteName = "admin_badpixxel_sendinblue_templates";

    /**
     * The base route pattern used to generate the routing information.
     *
     * @var string
     */
    protected $baseRoutePattern = "sib/templates";

    /**
     * Action list for the search result.
     *
     * @var string[]
     */
    protected $searchResultActions = array();

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('batch');
        $collection->remove('show');
        $collection->remove('edit');
        $collection->remove('export');
        $collection->remove('delete');

        $collection->add('view', '{emailCode}/view');
        $collection->add('update', '{emailCode}/update');
        $collection->add('send', '{emailCode}/send');
    }
}
