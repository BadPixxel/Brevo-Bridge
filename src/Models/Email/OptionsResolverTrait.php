<?php

namespace BadPixxel\BrevoBridge\Models\Email;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Manage Email Options Resolver
 */
trait OptionsResolverTrait
{
    private ?OptionsResolver $resolver = null;

    /**
     * Configure Options for Parameters Resolver
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     */
    protected function configureResolver(OptionsResolver $resolver): void
    {
    }

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
}