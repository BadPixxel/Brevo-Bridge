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

use BadPixxel\BrevoBridge\Controller\Templates\Sms;
use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * Admin Class for Brevo Bridge Sms Templates Management
 */
class SmsTemplatesAdmin extends Admin
{
    /**
     * The base route name used to generate the routing information.
     *
     * @var string
     */
    protected $baseRouteName = "admin_badpixxel_brevo_templates_sms";

    /**
     * The base route pattern used to generate the routing information.
     *
     * @var string
     */
    protected $baseRoutePattern = "brevo/templates/sms";

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

        $collection->add('list', 'list', array(
            "_controller" => Sms\ListView::class
        ));
        $collection->add('preview', '{smsId}/preview', array(
            "_controller" => Sms\Preview::class
        ));
        $collection->add('send', '{smsId}/send', array(
            "_controller" => Sms\Send::class
        ));
    }
}
