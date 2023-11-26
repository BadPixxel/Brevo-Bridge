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

/**
 * Common Interface for Emails that Provide Uses Html Templates
 */
interface HtmlTemplateAwareInterface
{
    /**
     * Get Raw Template ID.
     *
     * @return int
     */
    public static function getTemplateId(): int;
}