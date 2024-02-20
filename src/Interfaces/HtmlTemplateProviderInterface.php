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
 * Common Interface for Emails that Provide Html Template Contents
 */
interface HtmlTemplateProviderInterface
{
    /**
     * Get Html Template Path.
     *
     * @return string
     */
    public function getTemplateHtml(): string;

    /**
     * Get Templating Parameters
     *
     * @return array
     */
    public function getTemplateParameters(): array;
}
