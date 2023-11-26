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

namespace BadPixxel\BrevoBridge\Models\Email;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Manage Email Options Resolver
 */
trait OptionsResolverTrait
{
    private ?OptionsResolver $resolver = null;

    /**
     * Get or Create Parameters Resolver
     */
    public function getResolver(): OptionsResolver
    {
        //==============================================================================
        // Init Parameters Resolver
        if (!isset($this->resolver)) {
            $resolver = new OptionsResolver();
            $this->configureResolver($resolver);
            $this->resolver = $resolver;
        }

        return $this->resolver;
    }

    /**
     * Configure Options for Parameters Resolver
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array());
    }
}
