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

namespace BadPixxel\SendinblueBridge\Interfaces;

/**
 * Common Interface for Emails that Provide Html Template Contents
 */
interface HtmlTemplateProviderInterface
{
    /**
     * Get Raw Template ID.
     *
     * @return int
     */
    public static function getTemplateId(): int;

    /**
     * Get Raw Template Html Contents.
     *
     * @return string
     */
    public static function getTemplateHtml(): string;

    /**
     * Get Templating Render Parameters
     *
     * @return array
     */
    public static function getTemplateParameters(): array;
}
