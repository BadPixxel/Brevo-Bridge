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

use BadPixxel\BrevoBridge\Controller\Templates\Preview;
use BadPixxel\BrevoBridge\Controller\Templates\Send;
use BadPixxel\BrevoBridge\Controller\Templates\View;
use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

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
    protected $baseRouteName = "admin_badpixxel_brevo_templates";

    /**
     * The base route pattern used to generate the routing information.
     *
     * @var string
     */
    protected $baseRoutePattern = "brevo/templates";

    /**
     * Action list for the search result.
     *
     * @var string[]
     */
    protected $searchResultActions = array();

    /**
     * @param RouteCollectionInterface $collection
     *
     * @return void
     */
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
        $collection->remove('batch');
        $collection->remove('show');
        $collection->remove('edit');
        $collection->remove('export');
        $collection->remove('delete');

        $collection->add('view', '{emailCode}/view', array(
            "_controller" => View::class
        ));
        $collection->add('preview', '{emailCode}/preview', array(
            "_controller" => Preview::class
        ));
        $collection->add('update', '{emailCode}/update');
        $collection->add('send', '{emailCode}/send', array(
            "_controller" => Send::class
        ));
    }
}
