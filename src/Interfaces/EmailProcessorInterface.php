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

namespace BadPixxel\BrevoBridge\Interfaces;

use BadPixxel\BrevoBridge\Models\AbstractEmail;

/**
 * Interface for all Brevo Emails Processor
 */
interface EmailProcessorInterface
{
    /**
     * Check if this Processor Support this Email Class
     *
     * @param class-string $emailClass
     *
     * @return bool
     */
    public function supports(string $emailClass): bool;

    /**
     * Process this Email before Sending
     */
    public function process(AbstractEmail $email): void;
}
