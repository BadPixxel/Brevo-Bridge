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

use BadPixxel\BrevoBridge\Admin\Controller\Emails;
use BadPixxel\BrevoBridge\Models\AbstractEmail;
use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Admin Class for SendInBlue Bridge Emails Templates Management
 */
#[AutoconfigureTag(
    'sonata.admin',
    attributes: array(
        'model_class' => AbstractEmail::class,
        'manager_type' => 'orm',
        'label' => 'Emails Templates',
        'group' => 'Brevo',
        'icon' => '<i class="fa far fa-envelope"></i>',
        'global_search' => false,
    )
)]
class EmailsTemplatesAdmin extends Admin
{
    /**
     * The base route name used to generate the routing information.
     *
     * @var string
     */
    protected $baseRouteName = "admin_badpixxel_brevo_templates_emails";

    /**
     * The base route pattern used to generate the routing information.
     *
     * @var string
     */
    protected $baseRoutePattern = "brevo/templates/emails";

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
            "_controller" => Emails\ListView::class
        ));
        $collection->add('view', '{emailId}/view', array(
            "_controller" => Emails\View::class
        ));
        $collection->add('preview', '{emailId}/preview', array(
            "_controller" => Emails\Preview::class
        ));
        $collection->add('export', '{emailId}/export', array(
            "_controller" => Emails\Export::class
        ));
        $collection->add('send', '{emailId}/send', array(
            "_controller" => Emails\Send::class
        ));
    }
}
