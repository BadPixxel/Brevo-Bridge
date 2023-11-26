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

namespace BadPixxel\BrevoBridge\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig Extension to Render Brevo Emails from Templates.
 */
class BrevoBridgeExtension extends AbstractExtension
{
    /**
     * Get List of Available Filters.
     */
    public function getFilters(): array
    {
        return array(
            new TwigFilter('safe', array($this, 'safeToRaw'), array('is_safe' => array('all'))),
        );
    }

    /**
     * Marks a variable as safe, equivalent of raw in Brevo templates.
     */
    public function safeToRaw(string $string): string
    {
        return $string;
    }
}
