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

namespace BadPixxel\BrevoBridge\Models\Gdpr;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

if (trait_exists('BadPixxel\Paddock\System\MySql\Controller\GdprAdminActionsTrait')) {
    /**
     * Conditional GDPR Trait - Paddock Available.
     *
     * Uses Paddock GDPR Admin Actions when available.
     */
    trait ConditionalGdprTrait
    {
        use \BadPixxel\Paddock\System\MySql\Controller\GdprAdminActionsTrait;
    }
} else {
    /**
     * Conditional GDPR Trait - Paddock Not Available.
     *
     * Provides stub methods for GDPR batch actions.
     */
    trait ConditionalGdprTrait
    {
        /**
         * GDPR Check Batch Action Stub.
         *
         * @param ProxyQueryInterface $query
         *
         * @return RedirectResponse
         *
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function batchActionGdprCheck(ProxyQueryInterface $query): RedirectResponse
        {
            return new RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        /**
         * GDPR Fix Batch Action Stub.
         *
         * @param ProxyQueryInterface $query
         *
         * @return RedirectResponse
         *
         * @SuppressWarnings(PHPMD.UnusedFormalParameter)
         */
        public function batchActionGdprFix(ProxyQueryInterface $query): RedirectResponse
        {
            return new RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }
    }
}
